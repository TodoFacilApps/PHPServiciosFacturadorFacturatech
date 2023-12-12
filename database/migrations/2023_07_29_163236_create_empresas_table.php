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
        Schema::create('EMPRESA', function (Blueprint $table) {
            $table->id('Empresa')->unsigned();
            $table->string('Nombre', 100);
            $table->string('RazonSocial', 150);
            $table->integer('Estado')->unsigned();
            $table->date('Fecha');
            $table->decimal('Nit', 15, 0);
            $table->string('Telefono', 50);
            $table->string('Direccion', 250);
            $table->integer('TipoEmpresa')->unsigned();
            $table->integer('TipoSoftware')->unsigned();
            $table->string('Contacto', 80);
            $table->string('Email', 200);
            $table->string('Web', 100);
            $table->string('Latitud', 25);
            $table->string('Longitud', 25);
            $table->integer('Ciudad')->unsigned();
            $table->string('WhatsApp', 50);
            $table->string('FaceBook', 250);
            $table->string('Twitter', 250);
            $table->string('Youtube', 250);
            $table->string('Eslogan', 220);
            $table->string('UrlLogo', 400);
            $table->string('UrlPortada', 400);
            $table->text('Email_TO');
            $table->text('Email_CC');
            $table->text('Email_CCO');
            $table->integer('DecimalesCantidad')->unsigned();
            $table->integer('Cliente')->unsigned();
            $table->string('ServerIP', 20)->nullable();
            $table->string('CadenaConexion', 200)->nullable();
            $table->string('ComerceID', 70)->nullable();
            $table->integer('Usr')->unsigned();
            $table->string('UsrHora', 8);
            $table->date('UsrFecha');
            $table->integer('UnidadMedida')->unsigned();
            $table->string('RemitenteNombre', 100);
            $table->string('RemitenteEmail', 100);
            $table->integer('Pais')->unsigned();
            $table->integer('Region')->unsigned();
            $table->string('IpServidor', 20)->nullable();
            $table->string('IpProxy', 20)->nullable();
            $table->unique('Nombre');
            $table->index('TipoSoftware');
            $table->index('Ciudad');
            $table->index('TipoEmpresa');
            $table->index('Telefono');
            $table->index('Direccion');
            $table->index('Nit');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('EMPRESA');
    }
};
