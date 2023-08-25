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
        Schema::create('VENTA', function (Blueprint $table) {
            $table->id('Venta');
            $table->integer('Empresa');
            $table->integer('Sucursal');
            $table->integer('PuntoVenta');
            $table->integer('Cliente');
            $table->date('Fecha');
            $table->double('Total',2);
            $table->integer('Moneda');
        });

        Schema::create('VENTADETALLE', function (Blueprint $table) {
            $table->id('VentaDetalle');
            $table->integer('Venta');
            $table->integer('Cantidad');
            $table->integer('Producto');
            $table->integer('UnidadMedida');
            $table->double('PrecioVenta',2);
            $table->double('Descuento',2);
            $table->double('MontoVenta',2);
            $table->double('MontoDescuento',2);
            $table->double('SubTotal',2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('VENTA');
        Schema::dropIfExists('VENTADETALLE');
    }
};
