<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use App\Rules\Cnpj;
use Illuminate\Foundation\Http\FormRequest;

class SalvarClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cliente = $this->route('cliente');

        return $cliente instanceof Cliente
            ? $this->user()->can('update', $cliente)
            : $this->user()->can('create', Cliente::class);
    }

    public function rules(): array
    {
        return ['nome' => ['required', 'string', 'max:100'], 'cnpj' => ['required', new Cnpj], 'tenant_id' => ['prohibited']];
    }
}
