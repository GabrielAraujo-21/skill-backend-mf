<?php

use App\Models\Cliente;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function usuarioDoTenant(int $tenant): User
{
    $usuario = User::factory()->make();
    $usuario->tenant_id = $tenant;
    $usuario->save();

    return $usuario;
}

function clienteDoTenant(int $tenant, string $nome): Cliente
{
    $cliente = new Cliente(['nome' => $nome]);
    $cliente->tenant_id = $tenant;
    $cliente->save();

    return $cliente;
}

it('recusa acesso sem autenticação', function () {
    $this->getJson('/api/v1/clientes')->assertUnauthorized();
});

it('lista somente clientes do próprio tenant, sem expor ids internos', function () {
    clienteDoTenant(1, 'Visivel');
    clienteDoTenant(2, 'Oculto');
    Sanctum::actingAs(usuarioDoTenant(1));

    $this->getJson('/api/v1/clientes')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.nome', 'Visivel')
        ->assertJsonMissingPath('data.0.id')
        ->assertJsonMissingPath('data.0.tenant_id');
});

it('cria cliente com tenant derivado do usuário', function () {
    Sanctum::actingAs(usuarioDoTenant(7));

    $this->postJson('/api/v1/clientes', ['nome' => 'Novo'])->assertCreated();

    expect(Cliente::query()->sole()->tenant_id)->toBe(7);
});

it('rejeita tenant enviado no payload e nome ausente', function () {
    Sanctum::actingAs(usuarioDoTenant(7));

    $this->postJson('/api/v1/clientes', ['tenant_id' => 9])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['nome', 'tenant_id']);
});

it('nega alteração entre tenants e preserva o registro', function () {
    $cliente = clienteDoTenant(2, 'Original');
    Sanctum::actingAs(usuarioDoTenant(1));

    $this->putJson("/api/v1/clientes/{$cliente->codigo}", ['nome' => 'Invadido'])->assertForbidden();

    expect($cliente->fresh()->nome)->toBe('Original');
});

it('falha fechado para usuário sem contexto de tenant', function () {
    $cliente = clienteDoTenant(0, 'Sem contexto');
    Sanctum::actingAs(usuarioDoTenant(0));

    $this->putJson("/api/v1/clientes/{$cliente->codigo}", ['nome' => 'Alterado'])->assertForbidden();
});
