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
        Schema::create('assets_specifications', function (Blueprint $table) {
            $table->id('id_spec');
            $table->foreignId('id_asset')->constrained('assets', 'id_asset')->cascadeOnDelete();
            $table->string('processor');
            $table->integer('ram');
            $table->integer('storage');
            $table->string('os_version');
            $table->boolean('is_hdd')->default(false);
            $table->boolean('is_ssd')->default(false);
            $table->boolean('is_nvme')->default(false);
            $table->dateTime('datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets_specifications');
    }
};
