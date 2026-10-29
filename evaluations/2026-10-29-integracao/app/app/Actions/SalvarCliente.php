<?php

namespace App\Actions;

use App\Models\Cliente;
use App\Models\User;
use App\Rules\Cnpj;

class SalvarCliente
{
    public function executar(User $usuario, array $dados, ?Cliente $cliente = null): Cliente
    {
        abort_unless(filled($usuario->tenant_id), 403);
        $cliente ??= new Cliente;
        $cliente->fill(['nome' => trim($dados['nome']), 'cnpj' => Cnpj::normalizar($dados['cnpj'])]);
        $cliente->tenant_id = $usuario->tenant_id;
        $cliente->save();

        return $cliente;
    }
}
