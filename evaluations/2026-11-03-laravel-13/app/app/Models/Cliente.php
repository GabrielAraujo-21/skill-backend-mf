<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $codigo
 * @property int $tenant_id
 * @property string $nome
 */
#[Fillable(['nome'])]
class Cliente extends Model
{
    use HasUuids;

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['codigo'];
    }

    public function getRouteKeyName(): string
    {
        return 'codigo';
    }
}
