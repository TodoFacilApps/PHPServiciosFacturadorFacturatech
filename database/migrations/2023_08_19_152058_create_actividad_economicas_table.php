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
        Schema::create('ACTIVIDADECONOMICA', function (Blueprint $table) {
            $table->integer('ActividadEconomica');
            $table->integer('CodigoAnterior')->nullable();
            $table->string('DescripcionActividad', 200);
            $table->string('DescripcionComoUsar', 200);
            $table->text('DescripcionDetallada')->nullable();
            $table->integer('TipoActividadEconomica');
            $table->integer('Usr');
            $table->date('UsrFecha');
            $table->string('UsrHora', 10);
            $table->integer('ActividadEconomicaantiguo')->nullable();
            $table->integer('CodigoActividad');

            $table->primary('ActividadEconomica');
            $table->unique('DescripcionActividad');
            $table->unique('ActividadEconomicaantiguo');
            $table->index('TipoActividadEconomica');
            $table->index('CodigoActividad');
            $table->index('CodigoAnterior');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ACTIVIDADECONOMICA');
    }
};
