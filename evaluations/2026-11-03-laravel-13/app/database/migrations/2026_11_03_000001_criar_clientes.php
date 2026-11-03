<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $tabela) {
            $tabela->unsignedInteger('tenant_id')->default(0)->index();
        });

        Schema::create('clientes', function (Blueprint $tabela) {
            $tabela->id();
            $tabela->uuid('codigo')->unique();
            $tabela->unsignedInteger('tenant_id')->index();
            $tabela->string('nome', 120);
            $tabela->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');

        Schema::table('users', function (Blueprint $tabela) {
            $tabela->dropColumn('tenant_id');
        });
    }
};
