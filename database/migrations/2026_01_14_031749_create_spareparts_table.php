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
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id('id_sparepart');
            $table->enum('category', ['RAM', 'STORAGE']);
            $table->enum('sparepart_type', ['DDR3', 'DDR4', 'DDR5', 'SSD', 'HDD', 'NVME']);
            $table->string('sparepart_name');
            $table->integer('size');
            $table->decimal('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spareparts');
    }
};
