<?php

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Queue\Events\JobAttempted;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

it('usa os padrões endurecidos do esqueleto 13', function () {
    expect(app()->version())->toStartWith('13.')
        ->and(config('session.serialization'))->toBe('json')
        ->and(config('cache.serializable_classes'))->toBeFalse();
});

it('deriva nomes de sessão e cache com hífen', function () {
    expect(config('session.cookie'))->toEndWith('-session')
        ->and(config('cache.prefix'))->toEndWith('-cache-');
});

it('inclui PreventRequestForgery no grupo web e mantém VerifyCsrfToken como alias', function () {
    expect(Route::getMiddlewareGroups()['web'])->toContain(PreventRequestForgery::class)
        ->and(is_a(VerifyCsrfToken::class, PreventRequestForgery::class, true))->toBeTrue();
});

it('não reconstrói objetos arbitrários lidos do cache de arquivos', function () {
    $cache = Cache::store('file');
    $cache->put('objeto-lab-13', new ArrayObject(['a' => 1]), 60);

    $lido = $cache->pull('objeto-lab-13');

    expect($lido)->not->toBeInstanceOf(ArrayObject::class)
        ->and($lido)->toBeInstanceOf(__PHP_Incomplete_Class::class);
});

it('continua guardando arrays no cache de arquivos', function () {
    $cache = Cache::store('file');
    $cache->put('array-lab-13', ['a' => 1], 60);

    expect($cache->pull('array-lab-13'))->toBe(['a' => 1]);
});

it('expõe a exceção em JobAttempted', function () {
    $propriedades = array_map(
        fn (ReflectionProperty $p) => $p->getName(),
        (new ReflectionClass(JobAttempted::class))->getProperties()
    );

    expect($propriedades)->toContain('exception')->not->toContain('exceptionOccurred');
});
