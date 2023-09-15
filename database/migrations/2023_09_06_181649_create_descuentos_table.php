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
        Schema::create('DESCUENTO', function (Blueprint $table) {
            $table->id('Descuento');
            $table->integer('Empresa');
            $table->string('Nombre');
            $table->integer('Tipo');
            $table->double('Valor',8);
            $table->integer('Estado')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('DESCUENTO');
    }
};
