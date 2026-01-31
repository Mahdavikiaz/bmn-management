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
        Schema::table('recommendations', function (Blueprint $table) {
            $table->string('target_type')->nullable()->after('priority_level');
            $table->string('size_mode')->nullable()->after('target_type');
            $table->unsignedInteger('target_size_gb')->nullable()->after('size_mode');
            $table->decimal('target_multiplier', 8, 2)->nullable()->after('target_size_gb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->dropColumn(['target_type', 'size_mode', 'target_size_gb', 'target_multiplier']);
        });
    }
};
