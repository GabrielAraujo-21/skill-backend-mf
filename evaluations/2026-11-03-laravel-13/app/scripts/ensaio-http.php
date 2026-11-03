<?php

/*
 * Ensaio HTTP real do laboratório: sobe o servidor embutido do PHP, com chave e
 * banco SQLite descartáveis, e confere controles que PHPUnit/Pest não exercita.
 * Uso, na raiz do app: php scripts/ensaio-http.php
 */

$raiz = dirname(__DIR__);
$porta = 18770;
$base = "http://127.0.0.1:{$porta}";
$banco = $raiz.'/database/laboratorio.sqlite';
$log = $raiz.'/storage/logs/laboratorio.log';
$cookies = tempnam(sys_get_temp_dir(), 'lab');

if (is_link($banco)) {
    exit("Banco do laboratório inválido.\n");
}

$ambiente = [
    'APP_ENV' => 'local',
    'APP_DEBUG' => 'false',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('L', 32)),
    'APP_URL' => $base,
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => $banco,
    'SESSION_DRIVER' => 'file',
    'CACHE_STORE' => 'file',
    'QUEUE_CONNECTION' => 'sync',
    'LOG_CHANNEL' => 'lab',
    'LOG_DEPRECATIONS_CHANNEL' => 'lab',
    'LOG_LEVEL' => 'debug',
] + getenv();

file_put_contents($banco, '');
@unlink($log);

foreach (['migrate:fresh --seed --force --no-interaction', 'cache:clear --no-interaction'] as $comando) {
    $processo = proc_open(
        array_merge([PHP_BINARY, 'artisan'], explode(' ', $comando)),
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
        $raiz,
        $ambiente
    );
    $saida = stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);
    if (proc_close($processo) !== 0) {
        exit("Falha em artisan {$comando}:\n{$saida}\n");
    }
}

$servidor = proc_open(
    [PHP_BINARY, '-d', 'error_reporting=E_ALL', '-S', "127.0.0.1:{$porta}", '-t', $raiz.'/public',
        $raiz.'/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php'],
    [1 => ['file', sys_get_temp_dir().'/lab-servidor.out', 'w'], 2 => ['file', sys_get_temp_dir().'/lab-servidor.err', 'w']],
    $pipes,
    $raiz.'/public',
    $ambiente
);

for ($i = 0; $i < 50 && ! @fsockopen('127.0.0.1', $porta); $i++) {
    usleep(200000);
}

/**
 * @param  array<string, mixed>|null  $corpo
 * @param  list<string>  $cabecalhos
 * @return array{status: int, cabecalhos: string, corpo: string}
 */
function requisitar(string $metodo, string $url, ?array $corpo, array $cabecalhos, string $jar): array
{
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_CUSTOMREQUEST => $metodo,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_HTTPHEADER => array_merge(['Accept: application/json', 'Content-Type: application/json'], $cabecalhos),
        CURLOPT_POSTFIELDS => $corpo === null ? null : json_encode($corpo),
    ]);
    $resposta = (string) curl_exec($curl);
    $tamanho = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    // O cookie jar é gravado ao destruir o handle; sem curl_close, depreciado no PHP 8.5.
    unset($curl);

    return ['status' => $status, 'cabecalhos' => substr($resposta, 0, $tamanho), 'corpo' => substr($resposta, $tamanho)];
}

function tokenXsrf(string $jar): string
{
    foreach (file($jar) as $linha) {
        $campos = explode("\t", trim($linha));
        if (($campos[5] ?? '') === 'XSRF-TOKEN') {
            return urldecode($campos[6]);
        }
    }

    return '';
}

$resultados = [];
$conferir = function (string $caso, int $esperado, array $resposta) use (&$resultados) {
    $resultados[] = ['caso' => $caso, 'esperado' => $esperado, 'obtido' => $resposta['status'], 'ok' => $esperado === $resposta['status']];
};

$origem = "Origin: {$base}";
$credenciais = ['email' => 'ana@example.test', 'password' => 'senha-publica-do-laboratorio'];

$conferir('login sem token CSRF', 419, requisitar('POST', "{$base}/login", $credenciais, [$origem], $cookies));
$conferir('login com Sec-Fetch-Site cross-site e sem token', 419, requisitar('POST', "{$base}/login", $credenciais, [$origem, 'Sec-Fetch-Site: cross-site'], $cookies));
$conferir('login com Sec-Fetch-Site same-site e sem token (padrão)', 419, requisitar('POST', "{$base}/login", $credenciais, [$origem, 'Sec-Fetch-Site: same-site'], $cookies));

$cookieCsrf = requisitar('GET', "{$base}/sanctum/csrf-cookie", null, [$origem], $cookies);
$conferir('sanctum/csrf-cookie', 204, $cookieCsrf);
$nomeSessao = preg_match('/Set-Cookie: ([a-z0-9-]+-session)=/i', $cookieCsrf['cabecalhos'], $m) ? $m[1] : '';
$resultados[] = ['caso' => 'cookie de sessão com hífen', 'esperado' => 'laravel-session', 'obtido' => $nomeSessao, 'ok' => $nomeSessao === 'laravel-session'];

$conferir('listagem antes do login', 401, requisitar('GET', "{$base}/api/v1/clientes", null, [$origem], $cookies));
$conferir('login com X-XSRF-TOKEN', 200, requisitar('POST', "{$base}/login", $credenciais, [$origem, 'X-XSRF-TOKEN: '.tokenXsrf($cookies)], $cookies));

$lista = requisitar('GET', "{$base}/api/v1/clientes", null, [$origem], $cookies);
$conferir('listagem por sessão stateful', 200, $lista);
$dados = json_decode($lista['corpo'], true)['data'] ?? [];
$resultados[] = ['caso' => 'listagem contém só o tenant 1', 'esperado' => 'Cliente do tenant 1', 'obtido' => implode(',', array_column($dados, 'nome')), 'ok' => array_column($dados, 'nome') === ['Cliente do tenant 1']];

$conferir('POST API stateful sem token', 419, requisitar('POST', "{$base}/api/v1/clientes", ['nome' => 'Sem token'], [$origem], $cookies));
$conferir('POST API stateful com token inválido', 419, requisitar('POST', "{$base}/api/v1/clientes", ['nome' => 'Token ruim'], [$origem, 'X-CSRF-TOKEN: invalido'], $cookies));
$conferir('POST API stateful com X-XSRF-TOKEN', 201, requisitar('POST', "{$base}/api/v1/clientes", ['nome' => 'Com token'], [$origem, 'X-XSRF-TOKEN: '.tokenXsrf($cookies)], $cookies));
$conferir('POST API com Sec-Fetch-Site same-origin e sem token', 201, requisitar('POST', "{$base}/api/v1/clientes", ['nome' => 'Mesma origem'], [$origem, 'Sec-Fetch-Site: same-origin'], $cookies));
$conferir('payload com tenant_id', 422, requisitar('POST', "{$base}/api/v1/clientes", ['nome' => 'X', 'tenant_id' => 2], [$origem, 'X-XSRF-TOKEN: '.tokenXsrf($cookies)], $cookies));
$conferir('logout', 204, requisitar('POST', "{$base}/logout", null, [$origem, 'X-XSRF-TOKEN: '.tokenXsrf($cookies)], $cookies));
$conferir('listagem depois do logout', 401, requisitar('GET', "{$base}/api/v1/clientes", null, [$origem], $cookies));

$tentativas = [];
$jarErro = tempnam(sys_get_temp_dir(), 'lab');
requisitar('GET', "{$base}/sanctum/csrf-cookie", null, [$origem], $jarErro);
for ($i = 0; $i < 7; $i++) {
    $tentativas[] = requisitar('POST', "{$base}/login", ['email' => 'ana@example.test', 'password' => 'errada'], [$origem, 'X-XSRF-TOKEN: '.tokenXsrf($jarErro)], $jarErro)['status'];
}
$resultados[] = ['caso' => 'limite de login persistente entre requisições', 'esperado' => '422 x4, 429 x3 (o login aprovado antes ocupa 1 de 5)', 'obtido' => implode(',', $tentativas), 'ok' => $tentativas === [422, 422, 422, 422, 429, 429, 429]];

proc_terminate($servidor);
proc_close($servidor);
@unlink($cookies);
@unlink($jarErro);

$linhasLog = is_file($log) ? file($log) : [];
$depreciacoes = array_values(array_filter($linhasLog, fn ($l) => stripos($l, 'deprecat') !== false));
$erros = array_values(array_filter($linhasLog, fn ($l) => str_contains($l, '.ERROR:')));
$resultados[] = ['caso' => 'depreciações registradas no log do runtime', 'esperado' => 0, 'obtido' => count($depreciacoes), 'ok' => $depreciacoes === []];

$saida = [
    'php' => PHP_VERSION,
    'sistema' => PHP_OS_FAMILY,
    'erros_no_log' => count($erros),
    'aprovados' => count(array_filter($resultados, fn ($r) => $r['ok'])),
    'total' => count($resultados),
    'resultados' => $resultados,
    'amostra_depreciacoes' => array_map('trim', array_slice($depreciacoes, 0, 5)),
];

echo json_encode($saida, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
exit($saida['aprovados'] === $saida['total'] ? 0 : 1);
