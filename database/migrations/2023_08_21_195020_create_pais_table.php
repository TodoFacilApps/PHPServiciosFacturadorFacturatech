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
        Schema::create('PAIS', function (Blueprint $table) {
            $table->integer('Pais')->primary();
            $table->string('Nombre', 200);
            $table->char('Code2', 2);
            $table->char('Code3', 3);
            $table->integer('CodigoTelefonico');
            $table->string('Prioridad', 4);
            $table->integer('PedirCodigoPostal');
            $table->string('ZonaHoraria', 200)->nullable();
            $table->string('Idioma', 200)->nullable();
            $table->tinyInteger('Estado')->default(1);
            $table->string('Moneda', 50)->nullable();
            $table->string('Codigo', 50)->nullable();
            $table->string('Simbolo', 50)->nullable();
            $table->integer('Usr')->nullable()->default(1);
            $table->date('UsrFecha')->nullable()->default('2021-01-01');
            $table->string('UsrHora', 10)->nullable()->default('08:00:00');

            // Puedes agregar más columnas si es necesario

            $table->index('Pais');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PAIS');
    }
};
