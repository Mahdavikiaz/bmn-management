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
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('id_type')->after('id_asset')->constrained('asset_types', 'id_type')->cascadeOnDelete();
            $table->string('nup')->after('id_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('id_type')->after('id_asset')->constrained('asset_types', 'id_type')->cascadeOnDelete();
            $table->string('nup')->after('id_type');
        });
    }
};
