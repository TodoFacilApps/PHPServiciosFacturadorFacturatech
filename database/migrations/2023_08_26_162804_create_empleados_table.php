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
        //segundo usuario de manejo del sistema de ventas
        Schema::create('EMPLEADO', function (Blueprint $table) {
            $table->id('Empleado');
            $table->string('Nombre');
            $table->string('Apellido');
            $table->string('Documento');
            $table->integer('Cargo');
            $table->integer('EmpresaSucursal');
            $table->string('Telefono');

            $table->Integer('Usr');//super usuario quien registro al empleado
            $table->date('UsrFecha')->nullable()->default('2021-01-01');
            $table->string('UsrHora', 10)->nullable()->default('08:00:00');

            // verificando inicio de sesion para el ingreso al sistema
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('EMPLEADO');
    }
};
