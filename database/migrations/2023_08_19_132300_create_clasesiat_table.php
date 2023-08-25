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
        Schema::create('CLASESIAT', function (Blueprint $table) {
            $table->integer('ClaseSIAT');
            $table->string('Nombre', 35)->nullable();
            $table->integer('Usr')->nullable();
            $table->string('UsrHora', 8)->nullable();
            $table->date('UsrFecha')->nullable();
            $table->string('Campo', 50)->nullable();

            $table->primary('ClaseSIAT');
            $table->index('Nombre');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CLASESIAT');
    }
};
