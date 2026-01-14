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
        Schema::create('assets', function (Blueprint $table) {
            $table->id('id_asset');
            $table->string('bmn_code')->unique();
            $table->string('device_name');
            $table->enum('device_type', ['PC', 'Laptop']);
            $table->string('gpu');
            $table->string('ram_type');
            $table->year('procurement_year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
