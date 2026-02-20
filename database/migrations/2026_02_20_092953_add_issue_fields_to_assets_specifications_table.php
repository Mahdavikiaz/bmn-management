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
            $table->text('issue_note')->nullable()->after('os_version');
            $table->string('issue_image_uri', 2048)->nullable()->after('issue_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets_specifications', function (Blueprint $table) {
            $table->dropColumn(['issue_note', 'issue_image_uri']);
        });
    }
};
