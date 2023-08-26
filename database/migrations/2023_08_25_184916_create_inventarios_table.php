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
        Schema::create('INVENTARIO', function (Blueprint $table) {
            $table->id('Inventario');
            $table->integer('Sucursal');
            $table->integer('Producto');
            $table->integer('CantidadDisponible');
            $table->integer('Usr')->nullable()->default(1);
            $table->date('UsrFecha')->nullable()->default('2021-01-01');
            $table->string('UsrHora', 10)->nullable()->default('08:00:00');
        });

        Schema::create('INVENTARIOMOVIMIENTO', function (Blueprint $table) {
            $table->id('InventarioMovimiento');
            $table->integer('Sucursal');
            $table->integer('PuntoVenta');
            $table->integer('TipoMovimiento');
            $table->integer('Cantidad');
            $table->timestamp('FechaHora');
            $table->string('Nota');
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
        Schema::dropIfExists('INVENTARIO');
        Schema::dropIfExists('INVENTARIOMOVIMIENTO');
    }
};
