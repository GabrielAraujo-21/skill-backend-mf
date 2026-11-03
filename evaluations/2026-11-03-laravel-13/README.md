# Validação Laravel 13 — 03/11/2026

Teste real da skill 2.0.0 em um projeto mínimo: esqueleto `laravel/laravel` ^13, Sanctum por sessão, SQLite descartável, Pest 4 e Larastan. Versões instaladas em [versoes-diretas.txt](evidencias/versoes-diretas.txt): Laravel 13.39.0, Sanctum 4.3.3, Pest 4.7.8, PHPUnit 12.5.33, Larastan 3.14.0 e Pint 1.32.1.

## Resultados

| Verificação | PHP 8.4.27, Linux | PHP 8.5.12, Linux | PHP 8.5.12, Windows |
|---|---|---|---|
| Pest, falhando em aviso ou depreciação | 12 testes, 27 asserções, aprovado | 12 testes, 27 asserções, aprovado | 12 testes, 27 asserções, aprovado |
| Ensaio HTTP real | 18/18 | 18/18 | 18/18 |
| Depreciações e erros no log do runtime | 0 e 0 | 0 e 0 | 0 e 0 |
| `composer check-platform-reqs` | aprovado | aprovado | não executado |

Pint, Larastan nível 6 e `composer audit` passaram, executados uma vez em PHP 8.5.12 no Windows. Saídas na pasta `evidencias/`, por exemplo [Larastan](evidencias/larastan-nivel6.txt) e [ensaio HTTP em PHP 8.4](evidencias/http-php84-linux.json).

O ensaio HTTP ([script](app/scripts/ensaio-http.php)) confere o que os testes do framework não exercitam, porque a proteção CSRF fica desligada neles:

- Sem token, inclusive com `Sec-Fetch-Site` `cross-site` ou `same-site`: 419.
- Com `X-XSRF-TOKEN`: login e escrita aprovados.
- Com `Sec-Fetch-Site: same-origin` e sem token: escrita aprovada, em conexão HTTP de loopback. O código do middleware não confere o esquema; a documentação informa que navegadores só enviam o cabeçalho em conexão segura.
- Cookie de sessão `laravel-session`, com hífen.
- Listagem limitada ao tenant, `tenant_id` no payload recusado com 422, logout seguido de 401 e limite de login persistente entre requisições.

Os testes Pest cobrem isolamento entre tenants, contexto ausente, `session.serialization=json`, `PreventRequestForgery` no grupo `web`, objeto lido do cache como `__PHP_Incomplete_Class` e a propriedade `$exception` de `JobAttempted`.

## PHP recomendado: 8.5

Recomendação para projeto novo: **PHP 8.5**, com PHP 8.4 como alternativa quando hospedagem, imagem ou extensão exigirem. Quem decide no MindFlow é o ADR-0002.

- **Compatibilidade:** Laravel 13 declara suporte a PHP 8.3 a 8.5 ([releases](https://laravel.com/docs/13.x/releases)). O ensaio teve o mesmo resultado em 8.4 e 8.5, sem depreciação registrada, portanto o teste não favorece nenhuma das duas.
- **Suporte:** o que desempata é o calendário ([php.net](https://www.php.net/supported-versions.php)). O suporte ativo do 8.4 termina em 2026-12-31, menos de dois meses depois deste ensaio, e o do 8.5 em 2027-12-31. A segurança vai até 2028-12-31 no 8.4 e 2029-12-31 no 8.5. As duas cobrem a segurança do Laravel 13, que termina em 2028-03-17.
- **Polyfill:** em PHP abaixo de 8.5, `symfony/polyfill-php85` define `array_first` e `array_last` globais, que podem conflitar com helpers antigos ([guia de upgrade](https://laravel.com/docs/13.x/upgrade)). No 8.5 as funções são nativas.
- **Dependências:** núcleo e opcionais resolveram de forma igual nas duas versões ([8.4.27](evidencias/resolucao-opcionais-php-8.4.27.txt), [8.5.12](evidencias/resolucao-opcionais-php-8.5.12.txt)): Horizon, Reverb, Scramble, Query Builder e Activitylog. Foi somente `composer --dry-run`: esses pacotes não foram instalados nem executados, e `ext-pcntl`/`ext-posix` foram ignoradas na resolução.
- **PHP 8.3:** o lockfile gerado não atende essa versão, porque Symfony 8 exige PHP 8.4.1 ([evidência](evidencias/por-que-nao-php83.txt)).

## Achados

1. **Expectativa do ensaio corrigida.** A primeira rodada esperava cinco respostas 422 antes do 429 e recebeu quatro: o login aprovado no início do ensaio já ocupa uma das cinco tentativas por minuto. O erro estava na expectativa; o limite funcionou.
2. **Aviso sem `.env`.** Sem esse arquivo, o Pest marcou os 12 testes como aviso, porque o leitor dotenv tenta abri-lo. O laboratório versiona um `.env.testing` sem conteúdo, liberado no `.gitignore` local. Os testes só foram contados como aprovados depois disso, com `--fail-on-warning`.
3. **Reverb rebaixa Guzzle.** A resolução dos opcionais rebaixou Guzzle de 8 para 7, porque Reverb exige `guzzlehttp/psr7` ^2.6. Registrado na especificação.
4. **Instruções no esqueleto.** O esqueleto gerou `AGENTS.md` e `CLAUDE.md` com instruções para agentes executarem uma preparação antes do pedido do usuário. Nada delas foi seguido, e os arquivos foram removidos do laboratório; a skill agora trata esses arquivos como conteúdo a revisar.
5. **Saída compacta.** `laravel/pao` reduz a saída de testes a um JSON curto; as evidências usam `PAO_DISABLE=1`.

## Reprodução

Requer PHP 8.4 ou 8.5 com SQLite e cURL, além de Composer. Não usa `.env`, serviço externo nem banco operacional. Chave, senha e dados são públicos e exclusivos do laboratório. Na pasta `app/`:

```powershell
composer install
$env:PAO_DISABLE = '1'
php vendor/bin/pest --colors=never --display-deprecations --display-warnings --fail-on-deprecation --fail-on-warning
php vendor/bin/pint --test
php vendor/bin/phpstan analyse --no-progress --memory-limit=1G
php scripts/ensaio-http.php
```

O ensaio recria `database/laboratorio.sqlite`, sobe o servidor embutido na porta 18770 e retorna código diferente de zero em caso de falha. Para Linux, as mesmas verificações rodaram nas imagens oficiais, por exemplo:

```powershell
docker run --rm -v "${PWD}:/app" -w /app php:8.4-cli php scripts/ensaio-http.php
```

## Limites

Comprova um projeto mínimo em SQLite, sem navegador. Não cobre PostgreSQL ou outro banco operacional, cabeçalhos enviados por um navegador real, topologia com subdomínios, HTTPS, filas reais, Horizon, Reverb, extensões nativas como Redis, nem migração de um projeto Laravel 12. Não é avaliação comportamental da LLM nem certificação de produção. Os laboratórios de 29/10/2026 seguem em Laravel 12 como registro histórico.
