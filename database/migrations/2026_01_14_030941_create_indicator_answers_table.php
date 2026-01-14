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
        Schema::create('indicator_answers', function (Blueprint $table) {
            $table->id('id_answer');
            $table->foreignId('id_option')->constrained('indicator_options', 'id_option')->cascadeOnDelete();
            $table->foreignId('id_spec')->constrained('assets_specifications', 'id_spec')->cascadeOnDelete();
            $table->tinyInteger('star_rating');
            $table->dateTime('datetime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indicator_answers');
    }
};
