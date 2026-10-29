<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Exclusivamente laboratório: jamais usar chave pública fora deste fixture.
        config(['app.key' => 'base64:'.base64_encode(str_repeat('L', 32)), 'app.debug' => false,
            'database.default' => 'sqlite', 'database.connections.sqlite.url' => null, 'database.connections.sqlite.database' => $this->app->runningUnitTests() ? ':memory:' : database_path('laboratorio.sqlite'),
            'session.driver' => $this->app->runningUnitTests() ? 'array' : 'file',
            'session.cookie' => 'laboratorio_skills_session', 'session.domain' => null, 'session.secure' => false,
            'cache.default' => $this->app->runningUnitTests() ? 'array' : 'file',
            'cache.stores.file.path' => storage_path('framework/cache/data'),
            'cache.stores.file.lock_path' => storage_path('framework/cache/data'), 'queue.default' => 'sync', 'mail.default' => 'array']);
    }
}
