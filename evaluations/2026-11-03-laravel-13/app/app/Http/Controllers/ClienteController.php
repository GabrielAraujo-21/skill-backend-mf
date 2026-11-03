<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalvarClienteRequest;
use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ClienteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $usuario */
        $usuario = $request->user();

        return ClienteResource::collection(
            Cliente::query()->where('tenant_id', $usuario->tenant_id)->orderBy('id')->get()
        );
    }

    public function store(SalvarClienteRequest $request): JsonResponse
    {
        /** @var User $usuario */
        $usuario = $request->user();

        $cliente = new Cliente($request->safe()->only('nome'));
        $cliente->tenant_id = $usuario->tenant_id;
        $cliente->save();

        return ClienteResource::make($cliente)->response()->setStatusCode(201);
    }

    public function update(SalvarClienteRequest $request, Cliente $cliente): ClienteResource
    {
        Gate::authorize('update', $cliente);

        $cliente->update($request->safe()->only('nome'));

        return ClienteResource::make($cliente);
    }
}
