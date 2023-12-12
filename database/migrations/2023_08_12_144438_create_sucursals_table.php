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
        Schema::create('EMPRESASUCURSAL', function (Blueprint $table) {
            $table->integer('Empresa')->unsigned();
            $table->integer('Sucursal')->unsigned();
            $table->integer('CodigoSucursal')->default(0);
            $table->integer('Estado')->nullable()->default(1)->comment('1=Habilitado,2=Deshabilitado');
            $table->integer('TipoEmisionModo')->nullable()->default(1)->comment('1=Por Sucursal(Globa), 2=Por Punto Venta(Independiente)');
            $table->string('Telefono', 70)->default('0');
            $table->string('Direccion', 200)->default('S/D');
            $table->string('Localidad', 50)->default('Santa Cruz');
            $table->integer('CantidadDePuntoVenta')->nullable()->default(0);
            $table->string('ServerIPRadminVPN', 21)->default('0.0.0.0');
            $table->integer('SincronizarCUFDASucursal')->nullable()->default(0);
            $table->string('BaseDeDatos', 30)->default('0');
            $table->string('UserDataBase', 30)->default('0');
            $table->string('PassDataBase', 30)->default('0');
            $table->string('ServerDBLocal', 45)->nullable();
            $table->string('DBLocal', 60)->nullable();
            $table->string('UsrDBLocal', 45)->nullable();
            $table->string('PassDBLocal', 45)->nullable();
            $table->integer('TipoDBLocal')->nullable();
            $table->primary(['Empresa', 'Sucursal']);
            $table->foreign('Empresa')->references('Empresa')->on('EMPRESA')->onDelete('restrict')->onUpdate('restrict');
        });


        Schema::create('PUNTOVENTA', function (Blueprint $table) {
            $table->increments('PuntoVenta');
            $table->integer('CodigoPuntoVenta')->nullable();
            $table->string('Nombre', 50)->nullable();
            $table->integer('Estado')->nullable()->comment('1=Pendiente Registro en SIAT\n2=Habilitado\n3=Observado Registro en SIAT\n4=Inhabilitado');
            $table->string('Descripcion', 150)->nullable();
            $table->integer('CodigoAmbiente')->nullable();
            $table->integer('CodigoModalidad')->nullable();
            $table->integer('Sucursal')->nullable();
            $table->integer('CodigoSucursal')->nullable();
            $table->integer('CodigoTipoPuntoVenta')->nullable();
            $table->string('CodigoSistema', 80)->nullable();
            $table->integer('Cuis')->nullable();
            $table->decimal('Nit', 20, 0)->nullable();
            $table->integer('SolicitarCUFDPorDemon')->nullable()->default(1);
            $table->string('HoraSolicitudCUFDPorDemon', 8)->nullable()->default('01:00:00');
            $table->integer('Usr')->nullable()->default(1);
            $table->string('UsrHora', 8)->nullable()->default('08:00:00');
            $table->date('UsrFecha')->nullable()->default('2022-07-19');
            $table->unique('Nombre');
            $table->index('Estado');
            $table->index('Sucursal');
            $table->index('CodigoSucursal');
            $table->index('CodigoAmbiente');
            $table->index('CodigoModalidad');
            $table->index('CodigoTipoPuntoVenta');
            $table->index('CodigoSistema');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('EMPRESASUCURSAL');
        Schema::dropIfExists('PUNTOVENTA');
    }
};
