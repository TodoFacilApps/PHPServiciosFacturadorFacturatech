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
        Schema::create('EMPRESATOKEN', function (Blueprint $table) {
            $table->integer('Empresa')->unsigned();
            $table->integer('Serial')->unsigned();
            $table->integer('Estado')->nullable()->default(1);
            $table->integer('TipoToken')->nullable()->default(1);
            $table->date('FechaCreacion')->nullable();
            $table->date('FechaLimite')->nullable();
            $table->integer('MetodoCreacionToken')->nullable();
            $table->string('Titulo', 80);
            $table->text('TokenService');
            $table->text('TokenSecret');
            $table->string('UrlCallBack', 150)->nullable();
            $table->string('UrlReturn', 150)->nullable();
            $table->string('UrlFactura', 150)->nullable();
            $table->string('MensajeCliente', 100)->nullable();
            $table->primary(['Empresa', 'Serial']);
            $table->foreign('Empresa')->references('Empresa')->on('EMPRESA')->onDelete('RESTRICT')->onUpdate('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('EMPRESATOKEN');
    }
};
