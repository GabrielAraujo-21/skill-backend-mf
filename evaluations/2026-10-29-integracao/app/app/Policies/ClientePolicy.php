<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;

class ClientePolicy
{
    public function viewAny(User $usuario): bool
    {
        return filled($usuario->tenant_id);
    }

    public function create(User $usuario): bool
    {
        return $this->viewAny($usuario) && $usuario->pode_editar;
    }

    public function update(User $usuario, Cliente $cliente): bool
    {
        return $this->create($usuario) && $usuario->tenant_id === $cliente->tenant_id;
    }

    public function delete(User $usuario, Cliente $cliente): bool
    {
        return $this->update($usuario, $cliente);
    }
}
