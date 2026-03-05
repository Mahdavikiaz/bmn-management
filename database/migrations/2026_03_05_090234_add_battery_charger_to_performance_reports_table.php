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
            $table->tinyInteger('prior_baterai')->nullable()->after('prior_processor');
            $table->tinyInteger('prior_charger')->nullable()->after('prior_baterai');

            $table->text('recommendation_baterai')->nullable()->after('recommendation_processor');
            $table->text('recommendation_charger')->nullable()->after('recommendation_baterai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_reports', function (Blueprint $table) {
            $table->dropColumn([
                'prior_baterai',
                'prior_charger',
                'recommendation_baterai',
                'recommendation_charger',
            ]);
        });
    }
};
