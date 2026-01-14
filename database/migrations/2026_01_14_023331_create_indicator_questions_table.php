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
        Schema::create('indicator_questions', function (Blueprint $table) {
            $table->id('id_question');
            $table->enum('category', ['RAM', 'STORAGE', 'CPU']);
            $table->string('indicator_name');
            $table->text('question');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indicator_questions');
    }
};
