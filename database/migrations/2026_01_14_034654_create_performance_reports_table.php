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
        Schema::create('performance_reports', function (Blueprint $table) {
            $table->id('id_report');
            $table->foreignId('id_user')->constrained('users', 'id_user');
            $table->foreignId('id_asset')->constrained('assets', 'id_asset');
            $table->foreignId('id_spec')->constrained('assets_specifications', 'id_spec');
            $table->tinyInteger('prior_ram');
            $table->tinyInteger('prior_storage');
            $table->tinyInteger('prior_processor');
            $table->text('recommendation_ram');
            $table->text('recommendation_storage');
            $table->text('recommendation_processor');
            $table->decimal('upgrade_ram_price', 10, 2);
            $table->decimal('upgrade_storage_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_reports');
    }
};
