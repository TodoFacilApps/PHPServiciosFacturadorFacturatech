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
        Schema::create('EMPRESAUSUARIOPERSONAL', function (Blueprint $table) {
            $table->integer('Empresa')->unsigned();
            $table->integer('Serial')->unsigned();
            $table->integer('Usuario')->nullable();
            $table->integer('Sucursal')->nullable();
            $table->integer('Estado')->nullable();
            $table->primary(['Empresa','Serial']);
            $table->index('Empresa');
            $table->index('Usuario');

        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('EMPRESAUSUARIOPERSONAL');
    }
};
