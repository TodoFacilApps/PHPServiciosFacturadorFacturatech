<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipoClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('TIPOCLIENTE', function (Blueprint $table) {
            $table->id('TipoCliente');
            $table->integer('Empresa');
            $table->string('Descripcion');
            $table->boolean('PrecioPorMayor')->default(false);
            $table->boolean('PrecioOferta')->default(false);
            $table->boolean('PrecioRemate')->default(false);
            $table->integer('Estado')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('TIPOCLIENTE');
    }
}
