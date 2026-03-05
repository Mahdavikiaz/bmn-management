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
        // size jadi nullable (karena baterai/charger tidak pakai size)
        DB::statement("
            ALTER TABLE spareparts
            MODIFY size INT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // balikin size jadi not null
        DB::statement("
            ALTER TABLE spareparts
            MODIFY size INT NOT NULL
        ");
    }
};
