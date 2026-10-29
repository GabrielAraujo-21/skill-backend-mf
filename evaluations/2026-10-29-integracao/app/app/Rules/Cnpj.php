<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cnpj implements ValidationRule
{
    public static function normalizar(string $valor): string
    {
        return str_replace(['.', '/', '-'], '', strtoupper(trim($valor)));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^(?:[A-Z0-9]{12}[0-9]{2}|[A-Z0-9]{2}\.[A-Z0-9]{3}\.[A-Z0-9]{3}\/[A-Z0-9]{4}-[0-9]{2})$/iD', trim($value))) {
            $fail('Informe um CNPJ válido.');

            return;
        }
        $cnpj = self::normalizar($value);
        $base = substr($cnpj, 0, 12);
        foreach ([[5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2], [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]] as $pesos) {
            $soma = 0;
            foreach ($pesos as $indice => $peso) {
                $soma += (ord($base[$indice]) - 48) * $peso;
            }
            $resto = $soma % 11;
            $base .= (string) ($resto < 2 ? 0 : 11 - $resto);
        }
        if ($base !== $cnpj || preg_match('/^(.)\1{13}$/D', $cnpj)) {
            $fail('Informe um CNPJ válido.');
        }
    }
}
