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
        Schema::table('assets_specifications', function (Blueprint $table) {
            $table->string('owner_asset')->after('id_asset');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets_specifications', function (Blueprint $table) {
            $table->string('owner_asset')->after('id_asset');
        });
    }
};
