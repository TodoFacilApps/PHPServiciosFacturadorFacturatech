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
        Schema::create('CATALOGO', function (Blueprint $table) {
            $table->id('Catalogo');
            $table->integer('CodigoActividad');
            $table->string('CodigoNandina', 20)->nullable();
            $table->bigInteger('CodigoProducto');
            $table->string('DescripcionProducto', 500)->nullable();
            $table->date('FechaVigencia')->nullable();
            $table->decimal('Usr', 5, 0)->nullable();
            $table->string('UsrHora', 8)->nullable();
            $table->date('UsrFecha')->nullable();
            $table->integer('Estado')->default(1);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CATALOGO');
    }
};
