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
        Schema::create('PRODUCTO', function (Blueprint $table) {
            $table->id('Producto');
            $table->string('Codigo');
            $table->string('Nombre');
            $table->boolean('ControlStock');
            $table->integer('Stock');
            $table->integer('Estado')->default(1);
        });

        Schema::create('PRODUCTOINGESO', function (Blueprint $table) {
            $table->id('ProductoIngreso');
            $table->integer('Ingreso');
            $table->integer('Producto');
            $table->integer('UnidadMedida');
            $table->integer('Cantidad');
            $table->double('Precio',2);
            $table->double('Total',2);
            $table->integer('Estado')->default(1);
        });

        Schema::create('INGRESO', function (Blueprint $table) {
            $table->id('Ingreso');
            $table->date('Fecha');
            $table->integer('Proveedor');
            $table->integer('Usuario');
            $table->double('Costo',2);
            $table->integer('Estado')->default(1);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PRODUCTO');
        Schema::dropIfExists('PRODUCTOINGESO');
        Schema::dropIfExists('INGESO');
    }
};
