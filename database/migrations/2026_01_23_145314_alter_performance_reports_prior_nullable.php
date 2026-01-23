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
            $table->tinyInteger('prior_ram')->nullable()->change();
            $table->tinyInteger('prior_storage')->nullable()->change();
            $table->tinyInteger('prior_processor')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_reports', function (Blueprint $table) {
            $table->tinyInteger('prior_ram')->nullable()->change();
            $table->tinyInteger('prior_storage')->nullable()->change();
            $table->tinyInteger('prior_processor')->nullable()->change();
        });
    }
};
