<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tenant_id')->nullable();
            $table->boolean('pode_editar')->default(false);
            $table->string('papel')->default('Usuario');
        });
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->string('nome', 100);
            $table->string('cnpj', 14);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['tenant_id', 'pode_editar', 'papel']));
    }
};
