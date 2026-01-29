<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // tambah kolom action
        Schema::table('recommendations', function (Blueprint $table) {
            if (!Schema::hasColumn('recommendations', 'action')) {
                $table->text('action')->nullable()->after('category');
            }
        });

        // 2) pindahin isi description ke action
        DB::statement("UPDATE recommendations SET action = description WHERE action IS NULL");

        // 3) rename description jd explanation
        if (Schema::hasColumn('recommendations', 'description') && !Schema::hasColumn('recommendations', 'explanation')) {
            DB::statement("ALTER TABLE recommendations CHANGE description explanation TEXT NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // balikin explanation -> description, drop action

        if (Schema::hasColumn('recommendations', 'explanation') && !Schema::hasColumn('recommendations', 'description')) {
            DB::statement("ALTER TABLE recommendations CHANGE explanation description TEXT NOT NULL");
        }

        Schema::table('recommendations', function (Blueprint $table) {
            if (Schema::hasColumn('recommendations', 'action')) {
                $table->dropColumn('action');
            }
        });
    }
};
