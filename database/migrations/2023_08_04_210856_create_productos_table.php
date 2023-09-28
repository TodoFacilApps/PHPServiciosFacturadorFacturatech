<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->integer('Empresa');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PRODUCTOINGESO');
        Schema::dropIfExists('INGRESO');
    }
};
/*

    $table->id('Producto');
    $table->string('Nombre', 100);
    $table->string('Descripcion', 240);
    $table->integer('Empresa');
    $table->integer('Estado');
    $table->integer('ActividadEconomica');
    $table->string('CodigoProductoOrigen', 30);
    $table->integer('CatalogoImpuestos');
    $table->string('CodigoProductoEmpresa', 50);
    $table->decimal('Precio', 10, 2);
    $table->integer('TipoProductoEmpresa');
    $table->string('UrlImagen', 150);
    $table->decimal('PrecioPorMayor', 10, 2);
    $table->decimal('PrecioOferta', 10, 2);
    $table->integer('NumeroOpciones');
    $table->integer('NroVersion');
    $table->string('Posicion', 10);
    $table->integer('DecimalesCantidad');
    $table->integer('Usr');
    $table->string('UsrHora', 8);
    $table->date('UsrFecha');




    $table->integer('TipoProducto')->default(1);
    $table->integer('Unidad')->default(1);
    $table->decimal('PrecioRemate', 10, 2)->default(0.00);
    $table->tinyInteger('Novedad')->default(0);
    $table->tinyInteger('Oferta')->default(0);
    $table->decimal('Saldo', 10, 3)->default(0.000);
    $table->tinyInteger('ControlaStock')->default(0);
    $table->decimal('MaximoStock', 10, 3)->default(100.000);
    $table->integer('ClaseSiat')->nullable()->default(1);

*/
