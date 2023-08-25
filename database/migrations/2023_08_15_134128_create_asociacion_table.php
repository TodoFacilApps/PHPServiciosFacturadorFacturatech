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
        Schema::create('ASOCIACION', function (Blueprint $table) {
            $table->id('Asociacion');
            $table->string('Nombre', 70)->nullable()->unique()->comment('Nombre de la Asociacion y debe ser unica');
            $table->integer('Empresa')->nullable()->comment('Empresa Contribuyente al cual le daremos servicio de facturacion');
            $table->decimal('NitEmpresa', 15, 0)->nullable();
            $table->integer('Estado')->nullable()->default(1);
            $table->integer('ModoCertificacion')->nullable()->default(0);
            $table->date('FechaAsociacion')->nullable()->default('1001-01-01');
            $table->date('FechaVigencia')->nullable()->default('1001-01-01');
            $table->integer('Certificacion')->nullable()->comment('Sistema el cual se usara para facturar al contribuyente');
            $table->string('CodigoSistema', 50)->nullable();
            $table->integer('CodigoAmbiente')->nullable()->default(1);
            $table->integer('TipoModalidad')->nullable();
            $table->integer('TipoServicio')->nullable();
            $table->integer('SolicitarCUFDPorDemon')->nullable()->default(0)->comment('Solicitara Para CodigoSurcursal = 0 y CodigoPuntoVenta = 0 (CasaMatriz sin Punto de Venta)');
            $table->string('HoraSolicitudCUFDPorDemon', 8)->nullable()->default('01:00:00')->comment('Hora de solicitud de CUFD en el que va actuar el Demon');
            $table->string('AsociacionCredencial', 128)->nullable()->comment('sha256');
            $table->string('Login', 50)->nullable();
            $table->string('Correos', 240)->nullable();
            $table->integer('Usr')->nullable()->default(1);
            $table->string('UsrHora', 8)->nullable()->default('08:00:00');
            $table->date('UsrFecha')->nullable()->default('1001-01-01');

            $table->index('Nombre');
            $table->index('Empresa');
            $table->index('Certificacion');
            $table->index('CodigoSistema');
            $table->index('CodigoAmbiente');
            $table->index('TipoModalidad');
            $table->index('TipoServicio');
            $table->index('Estado');
            $table->index('NitEmpresa');
            $table->index('AsociacionCredencial');
        });    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ASOCIACION');
    }
};
