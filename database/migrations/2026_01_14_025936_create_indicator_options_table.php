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
        Schema::create('indicator_options', function (Blueprint $table) {
            $table->id('id_option');
            $table->foreignId('id_question')->constrained('indicator_questions', 'id_question')->cascadeOnDelete();
            $table->string('label');
            $table->text('option');
            $table->tinyInteger('star_value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indicator_options');
    }
};
