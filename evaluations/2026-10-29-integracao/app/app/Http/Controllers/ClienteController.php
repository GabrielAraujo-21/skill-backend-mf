<?php

namespace App\Http\Controllers;

use App\Actions\SalvarCliente;
use App\Http\Requests\SalvarClienteRequest;
use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Cliente::class);

        return ClienteResource::collection(Cliente::where('tenant_id', $request->user()->tenant_id)->orderBy('id')->get());
    }

    public function store(SalvarClienteRequest $request, SalvarCliente $salvar)
    {
        return (new ClienteResource($salvar->executar($request->user(), $request->validated())))->response()->setStatusCode(201);
    }

    public function update(SalvarClienteRequest $request, Cliente $cliente, SalvarCliente $salvar)
    {
        return new ClienteResource($salvar->executar($request->user(), $request->validated(), $cliente));
    }

    public function destroy(Cliente $cliente)
    {
        Gate::authorize('delete', $cliente);
        $cliente->delete();

        return response()->noContent();
    }
}
