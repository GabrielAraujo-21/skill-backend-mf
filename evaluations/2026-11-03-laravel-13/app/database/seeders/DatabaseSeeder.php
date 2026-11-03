<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([1 => 'ana@example.test', 2 => 'bruno@example.test'] as $tenant => $email) {
            $usuario = User::factory()->make(['email' => $email, 'password' => 'senha-publica-do-laboratorio']);
            $usuario->tenant_id = $tenant;
            $usuario->save();

            $cliente = new Cliente(['nome' => "Cliente do tenant {$tenant}"]);
            $cliente->tenant_id = $tenant;
            $cliente->save();
        }
    }
}
