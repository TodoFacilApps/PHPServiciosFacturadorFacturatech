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
        Schema::create('PROVEEDOR', function (Blueprint $table) {
            $table->id('Proveedor');
            $table->integer('TipoDocumento');
            $table->integer('Documenton');
            $table->string('Nombre');
            $table->integer('CodigoInterno');
            $table->string('Correo');
            $table->string('DomicilioFiscal')->nullable();
            $table->string('ContactoFiscal');
            $table->string('NombrePersonalAcargo');
            $table->string('ContactoPersonalAcargo')->nullable();
            $table->integer('Empresa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PROVEEDOR');
    }
};
