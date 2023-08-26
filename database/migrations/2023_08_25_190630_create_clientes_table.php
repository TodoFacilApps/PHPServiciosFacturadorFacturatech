<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('CLIENTE', function (Blueprint $table) {
            $table->id('Cliente');
            $table->integer('Empresa');
            $table->string('Nombre');
            $table->string('Apellidos');
            $table->integer('TipoDocumento');
            $table->string('Documento');
            $table->string('Direccion')->nullable();
            $table->string('Email')->nullable();
            $table->string('Telefono')->nullable();
            $table->integer('Usr')->nullable()->default(1);
            $table->date('UsrFecha')->nullable()->default('2021-01-01');
            $table->string('UsrHora', 10)->nullable()->default('08:00:00');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CLIENTE');
    }
};
