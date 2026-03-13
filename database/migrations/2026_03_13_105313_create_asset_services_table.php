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
        Schema::create('asset_services', function (Blueprint $table) {
            $table->id('id_service');
            $table->foreignId('id_asset')->constrained('assets', 'id_asset')->cascadeOnDelete();
            $table->date('service_date');
            $table->text('service_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_services');
    }
};
