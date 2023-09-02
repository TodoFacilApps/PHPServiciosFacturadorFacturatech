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
        Schema::create('MOVIMIENTO', function (Blueprint $table) {
            $table->id('Movimiento');
            $table->integer('Empresa');
            $table->integer('Sucursal');
            $table->integer('PuntoVenta');
            $table->string('Producto');
            $table->integer('Cantidad');
            $table->integer('TipoMovimiento');
            $table->string('Motivo');
            $table->date('Fecha');
            $table->integer('Usr')->nullable()->default(1);
            $table->string('UsrHora', 8)->nullable()->default('08:00:00');
            $table->date('UsrFecha')->nullable()->default('1001-01-01');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('MOVIMIENTO');
    }
};
