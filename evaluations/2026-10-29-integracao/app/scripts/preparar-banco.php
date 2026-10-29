<?php

use Illuminate\Contracts\Console\Kernel;

// Exclusivo do laboratório descartável. Nunca apontar para um backend consumidor.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
$esperado = realpath(__DIR__.'/../database').DIRECTORY_SEPARATOR.'laboratorio.sqlite';
$atual = config('database.connections.sqlite.database');
if (config('database.default') !== 'sqlite' || realpath(dirname($atual)).DIRECTORY_SEPARATOR.basename($atual) !== $esperado || is_link($atual)) {
    fwrite(STDERR, "Recusado: banco fora do laboratório.\n");
    exit(1);
}
if (! is_file($atual)) {
    touch($atual);
}
$codigo = $kernel->call('migrate:fresh', ['--seed' => true, '--force' => true]);
echo $kernel->output();
exit($codigo);
