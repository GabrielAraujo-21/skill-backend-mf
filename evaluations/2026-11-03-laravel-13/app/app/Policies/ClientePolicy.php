<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;

class ClientePolicy
{
    public function update(User $usuario, Cliente $cliente): bool
    {
        return $usuario->tenant_id > 0 && $usuario->tenant_id === $cliente->tenant_id;
    }
}
