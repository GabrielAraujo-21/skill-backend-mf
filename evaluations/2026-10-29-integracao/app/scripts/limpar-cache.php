<?php

use Illuminate\Contracts\Console\Kernel;

// Executar somente no laboratório para isolar quotas entre casos do navegador.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
$raiz = realpath(__DIR__.'/..');
$esperado = $raiz.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'data';
$atual = config('cache.stores.file.path');
$recusar = static function (): never {
    fwrite(STDERR, "Recusado: cache não é arquivo local seguro deste laboratório.\n");
    exit(1);
};
if (config('cache.default') !== 'file' || config('cache.stores.file.driver') !== 'file' || ! is_string($atual) || realpath($atual) !== $esperado) {
    $recusar();
}
$caminho = $raiz;
foreach (['storage', 'framework', 'cache', 'data'] as $parte) {
    $caminho .= DIRECTORY_SEPARATOR.$parte;
    if (is_link($caminho) || realpath($caminho) !== $caminho) {
        $recusar();
    }
}
// Não seguir junctions/symlinks nem apagar alvos fora da raiz validada.
$itens = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($esperado, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
foreach ($itens as $item) {
    $resolvido = $item->getRealPath();
    if ($item->isLink() || $resolvido === false || ! str_starts_with($resolvido, $esperado.DIRECTORY_SEPARATOR)) {
        $recusar();
    }
}
$codigo = $kernel->call('cache:clear', ['store' => 'file']);
echo $kernel->output();
exit($codigo);
