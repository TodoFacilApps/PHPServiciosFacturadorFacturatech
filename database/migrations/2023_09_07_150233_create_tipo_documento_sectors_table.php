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
        Schema::create('TIPODOCUMENTOSECTOR', function (Blueprint $table) {
            $table->id('TipoDocumentoSector');
            $table->string('NOMBRE', 80);
            $table->integer('ServicioWeb')->nullable()->default(0);
            $table->integer('TipoDocumentoFiscal');
            $table->integer('TipoFacturaDocumento')->nullable();
            $table->string('Caracteristicas', 255)->nullable();
            $table->string('TablaAConsultar', 45)->nullable();
            $table->string('DetalleAConsultar', 45)->nullable();
            $table->string('FormatoReporte', 45)->nullable();
            $table->string('URLIMAGEN', 75);
            $table->string('POSICION', 4);
            $table->string('TituloDocumento', 150)->default('FACTURA');
            $table->string('SubTituloDocumento', 150)->default('CON DERECHO A CRÉDITO FISCAL');
            $table->integer('DigitosFactura')->nullable();
            $table->integer('DecimalesFactura')->nullable();
            $table->integer('DigitosFacturaDetalle')->nullable();
            $table->integer('DecimalesFacturaDetalle')->nullable();
            $table->integer('Decimales')->comment('Decimales para las columnas de FAFACTURADETALLE, como Cantidad, PrecioUnitario, Descuento, SubTotal')->default(0);
            $table->integer('Usr');
            $table->string('UsrHora', 8);
            $table->date('UsrFecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('TIPODOCUMENTOSECTOR');
    }
};
