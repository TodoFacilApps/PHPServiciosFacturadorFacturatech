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
        Schema::create('TOKENSERVICIO', function (Blueprint $table) {
            $table->id('TokenServicio');
            $table->string('ApiToken');
            $table->string('TokenService');
            $table->string('TokenSecret');
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
