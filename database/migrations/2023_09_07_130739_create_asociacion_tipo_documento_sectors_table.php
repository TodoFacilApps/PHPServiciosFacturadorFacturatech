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
        Schema::create('ASOCIACIONTIPODOCUMENTOSECTOR', function (Blueprint $table) {
            $table->integer('Asociacion')->unsigned();
            $table->integer('Serial')->unsigned();
            $table->integer('TipoDocumentoSector')->unsigned();
            $table->integer('Habilitado')->default(1);
            $table->primary(['Asociacion', 'Serial', 'TipoDocumentoSector']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ASOCIACIONTIPODOCUMENTOSECTOR');
    }
};
