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
        Schema::create('UNIDADMEDIDA', function (Blueprint $table) {
            $table->id('UnidadMedida');
            $table->integer('Empresa');
            $table->integer('Codigo')->nullable();
            $table->string('Descripcion', 100)->nullable();
            $table->string('Abreviatura', 5)->nullable();
            $table->integer('Usr')->default(1)->nullable();
            $table->date('UsrFecha')->nullable();
            $table->string('UsrHora', 10)->nullable();
            $table->unique('Codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('UNIDADMEDIDA');
    }
};
