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
        Schema::create('CERTIFICACIONTIPODOCUMENTOSECTOR', function (Blueprint $table) {
            $table->integer('Certificacion');
            $table->integer('Serial');
            $table->integer('TipoDocumentoSector');
            $table->primary(['Certificacion', 'Serial']);
            $table->unique(['Certificacion', 'TipoDocumentoSector']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CERTIFICACIONTIPODOCUMENTOSECTOR');
    }
};
