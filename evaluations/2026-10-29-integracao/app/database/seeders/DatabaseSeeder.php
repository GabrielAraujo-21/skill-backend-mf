<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['a', 'A', true], ['b', 'B', true], ['leitor', 'A', false]] as [$nome, $tenant, $editar]) {
            User::updateOrCreate(['email' => $nome.'@example.test'], ['name' => 'Usuário '.$nome, 'password' => Hash::make('Teste123456!'), 'tenant_id' => $tenant, 'pode_editar' => $editar]);
        }
        foreach (['A', 'B'] as $tenant) {
            $cliente = Cliente::firstOrNew(['tenant_id' => $tenant, 'nome' => 'Cliente '.$tenant]);
            $cliente->tenant_id = $tenant;
            $cliente->nome = 'Cliente '.$tenant;
            $cliente->cnpj = '11222333000181';
            $cliente->save();
        }
    }
}
