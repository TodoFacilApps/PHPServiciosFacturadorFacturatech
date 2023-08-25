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
        Schema::create('IMPUESTOIVA', function (Blueprint $table) {
            $table->id('ImpuestoIVA');
            $table->string('Porcentaje');
            $table->double('Valor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('IMPUESTOIVA');
    }
};
