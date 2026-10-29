<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientesTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(?string $tenant = 'A', bool $editar = true): User
    {
        return User::factory()->create(['tenant_id' => $tenant, 'pode_editar' => $editar, 'papel' => 'SuperAdmin']);
    }

    private function cliente(string $tenant = 'A'): Cliente
    {
        $cliente = new Cliente(['nome' => 'Cliente '.$tenant, 'cnpj' => '11222333000181']);
        $cliente->tenant_id = $tenant;
        $cliente->save();

        return $cliente;
    }

    public function test_anonimo_nao_le_clientes(): void
    {
        $this->getJson('/api/clientes')->assertUnauthorized();
    }

    public function test_login_invalido_preserva_contrato_422(): void
    {
        $this->postJson('/api/login', ['email' => 'a@example.test', 'password' => 'incorreta'])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_login_regenera_sessao_e_logout_invalida(): void
    {
        $usuario = $this->usuario();
        $this->postJson('/api/login', ['email' => $usuario->email, 'password' => 'password'])->assertOk()->assertJsonPath('tenant_id', 'A');
        $this->assertAuthenticatedAs($usuario);
        $this->postJson('/api/logout')->assertNoContent();
        $this->assertGuest('web');
    }

    public function test_listagem_filtra_tenant_e_preserva_resource(): void
    {
        $a = $this->cliente();
        $this->cliente('B');
        $this->actingAs($this->usuario())->getJson('/api/clientes')->assertOk()->assertJsonCount(1, 'data')->assertExactJson(['data' => [['id' => $a->id, 'nome' => 'Cliente A', 'cnpj' => '11222333000181']]]);
    }

    public function test_contexto_ausente_falha_fechado(): void
    {
        $this->actingAs($this->usuario(null))->getJson('/api/clientes')->assertForbidden();
    }

    public function test_superadmin_sem_permissao_nao_cria(): void
    {
        $this->actingAs($this->usuario('A', false))->postJson('/api/clientes', ['nome' => 'Teste', 'cnpj' => '11222333000181'])->assertForbidden();
        $this->assertDatabaseCount('clientes', 0);
    }

    public function test_cnpj_numerico_persistido_com_zeros(): void
    {
        $this->actingAs($this->usuario())->postJson('/api/clientes', ['nome' => 'Nome', 'cnpj' => '00.000.000/0001-91'])->assertCreated()->assertJsonPath('data.cnpj', '00000000000191');
        $this->assertDatabaseHas('clientes', ['tenant_id' => 'A', 'cnpj' => '00000000000191']);
    }

    public function test_cnpj_alfanumerico_e_normalizado(): void
    {
        $this->actingAs($this->usuario())->postJson('/api/clientes', ['nome' => 'Nome', 'cnpj' => '12.abc.345/01de-35'])->assertCreated()->assertJsonPath('data.cnpj', '12ABC34501DE35');
    }

    public function test_cnpj_invalido_retorna_erros_por_campo(): void
    {
        $this->actingAs($this->usuario())->postJson('/api/clientes', ['nome' => '', 'cnpj' => '12ABC34501DE36'])->assertUnprocessable()->assertJsonValidationErrors(['nome', 'cnpj']);
        $this->assertDatabaseCount('clientes', 0);
    }

    public function test_tenant_enviado_no_payload_e_recusado(): void
    {
        $this->actingAs($this->usuario())->postJson('/api/clientes', ['nome' => 'Nome', 'cnpj' => '11222333000181', 'tenant_id' => 'B'])->assertUnprocessable()->assertJsonValidationErrors('tenant_id');
    }

    public function test_cross_tenant_nao_altera_nem_exclui(): void
    {
        $cliente = $this->cliente('B');
        $this->actingAs($this->usuario());
        $this->putJson('/api/clientes/'.$cliente->id, ['nome' => 'Invasao', 'cnpj' => '11222333000181'])->assertForbidden();
        $this->deleteJson('/api/clientes/'.$cliente->id)->assertForbidden();
        $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'nome' => 'Cliente B']);
    }

    public function test_atualizacao_e_exclusao_autorizadas(): void
    {
        $cliente = $this->cliente();
        $this->actingAs($this->usuario());
        $this->putJson('/api/clientes/'.$cliente->id, ['nome' => 'Alterado', 'cnpj' => '11222333000181'])->assertOk()->assertJsonPath('data.id', $cliente->id)->assertJsonPath('data.nome', 'Alterado');
        $this->deleteJson('/api/clientes/'.$cliente->id)->assertNoContent();
        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }

    public function test_rota_legada_preserva_uri_metodo_e_campos(): void
    {
        $this->actingAs($this->usuario());
        $resposta = $this->postJson('/api/clientes', ['nome' => '  Nome legado  ', 'cnpj' => '11222333000181'])->assertCreated();
        $resposta->assertJsonStructure(['data' => ['id', 'nome', 'cnpj']]);
        $this->getJson('/api/clientes')->assertOk()->assertJsonPath('data.0.nome', 'Nome legado');
    }
}
