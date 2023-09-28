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
        Schema::create('UNIDADMEDIDAEMPRESA', function (Blueprint $table) {
            $table->id('UnidadMedida');
            $table->integer('Empresa');
            $table->integer('Codigo');
            $table->integer('Estado');
            $table->integer('Usr')->default(1)->nullable();
            $table->date('UsrFecha')->nullable();
            $table->string('UsrHora', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('UNIDADMEDIDAEMPRESA');
    }
};
