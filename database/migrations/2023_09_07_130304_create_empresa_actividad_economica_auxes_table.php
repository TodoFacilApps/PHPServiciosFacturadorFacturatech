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
        Schema::create('EMPRESAACTIVIDADECONOMICAAUX', function (Blueprint $table) {
            $table->integer('Empresa')->unsigned();
            $table->integer('ActividadEconomica')->unsigned();
            $table->integer('Estado')->default(1);
            $table->primary(['Empresa', 'ActividadEconomica']);
            $table->foreign('Empresa')->references('Empresa')->on('EMPRESA')->onDelete('restrict')->onUpdate('restrict');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('EMPRESAACTIVIDADECONOMICAAUX');
    }
};
