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
        // tambah kategori baru
        DB::statement("
            ALTER TABLE spareparts
            MODIFY category ENUM('RAM','STORAGE','BATERAI','CHARGER')
        ");

        // tambah type baru untuk baterai/charger
        DB::statement("
            ALTER TABLE spareparts
            MODIFY sparepart_type ENUM('DDR3','DDR4','DDR5','SSD','HDD','NVME','BATTERY','ADAPTER')
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
