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
        Schema::create('USUARIO', function (Blueprint $table) {
            $table->id('Usuario');
            $table->string('Nombre', 100)->nullable();
            $table->string('Apellido', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('Correo', 150)->nullable();
            $table->string('password', 190)->nullable();
            $table->integer('Telefono')->nullable();
            $table->integer('Usr')->nullable();
            $table->date('UsrFecha')->nullable();
            $table->string('UsrHora', 12)->nullable();
            $table->unique('Correo');
            $table->unique('email');
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('USUARIO');
    }
};
