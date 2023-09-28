<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('USUARIO', function (Blueprint $table) {
            $table->id('Usuario');
            $table->string('Nombre', 100)->nullable();
            $table->string('Apellido', 100)->nullable();
            $table->string('email')->unique();
            $table->string('CorreoRespaldo', 150)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->integer('Telefono')->nullable();
            $table->string('Perfil')->default('normal');
            $table->string('Estilo')->default('normal');
            $table->integer('Usr')->nullable();
            $table->date('UsrFecha')->nullable();
            $table->string('UsrHora', 12)->nullable();
            $table->rememberToken();
            $table->integer('EmpresaSeleccionada')->nullable()->default(null);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('USUARIO');
    }
}
