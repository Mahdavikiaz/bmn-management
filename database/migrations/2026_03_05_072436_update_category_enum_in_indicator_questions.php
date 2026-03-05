<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE indicator_questions CHANGE category category ENUM('RAM', 'STORAGE', 'CPU', 'BATERAI', 'CHARGER')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE indicator_questions CHANGE category category ENUM('RAM', 'STORAGE', 'CPU')");
    }
};
