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
    {// este fin de semana revisar esto y adaptarlo con la tabla Empresa Token
        Schema::create('TOKENSERVICIO', function (Blueprint $table) {
            $table->id('TokenServicio');
            $table->integer('Empresa')->uniqid();
            $table->text('TokenService');
            $table->text('TokenSecret');
            $table->text('TokenBearer');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('TOKENSERVICIO');
    }
};
