<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SessaoController extends Controller
{
    public function login(Request $request)
    {
        $credenciais = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt($credenciais)) {
            throw ValidationException::withMessages(['email' => ['Credenciais inválidas.']]);
        }
        $request->session()->regenerate();

        return $this->me($request);
    }

    public function me(Request $request)
    {
        $usuario = $request->user();

        return response()->json(['id' => $usuario->id, 'nome' => $usuario->name, 'tenant_id' => $usuario->tenant_id]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
