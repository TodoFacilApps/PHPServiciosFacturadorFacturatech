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
        Schema::create('CERTIFICACION', function (Blueprint $table) {
            $table->id('Certificacion');
            $table->integer('Empresa')->default(1);
            $table->string('RazonSocial', 100)->nullable();
            $table->decimal('Nit', 20, 0)->nullable();
            $table->string('NombreSistema', 100)->nullable();
            $table->string('VersionSistema', 20)->default('');
            $table->integer('TipoSistema')->nullable();
            $table->integer('CodigoAmbiente')->nullable()->default(2);
            $table->integer('CodigoModalidad')->nullable()->default(1);
            $table->string('CodigoSistema', 50)->nullable();
            $table->integer('Estado')->nullable();
            $table->integer('Etapa')->default(1)->comment('1=Iniciado, 2=Pruebas, 3=Inspeccion, 4=Autorizado[Produccion], 5=Cerrado');
            $table->date('FechaSolicitud')->nullable();
            $table->string('HoraSolcitud', 8)->nullable();
            $table->date('FechaCertificacion')->nullable();
            $table->integer('SolicitudSIAT')->nullable();
            $table->date('FechaCorteSIAT')->default('1001-01-01');
            $table->string('HoraCorteSIAT', 8)->default('08:00:00');
            $table->integer('Usr')->nullable();
            $table->string('UsrHora', 8)->nullable();
            $table->date('UsrFecha')->nullable();

            $table->index('TipoSistema');
            $table->index('CodigoAmbiente');
            $table->index('CodigoModalidad');
            $table->index('CodigoSistema');
            $table->index('Empresa');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CERTIFICACION');
    }
};
