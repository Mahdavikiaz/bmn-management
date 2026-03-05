<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE recommendations
            MODIFY category ENUM('RAM','STORAGE','CPU','BATERAI','CHARGER') NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE recommendations
            MODIFY category ENUM('RAM','STORAGE','CPU') NOT NULL
        ");
    }
};
