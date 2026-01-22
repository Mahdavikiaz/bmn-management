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
        Schema::table('performance_reports', function (Blueprint $table) {
            $table->decimal('upgrade_ram_price', 10, 2)->nullable()->change();
            $table->decimal('upgrade_storage_price', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_reports', function (Blueprint $table) {
            $table->decimal('upgrade_ram_price', 10, 2)->nullable(false)->change();
            $table->decimal('upgrade_storage_price', 10, 2)->nullable(false)->change();
        });
    }
};
