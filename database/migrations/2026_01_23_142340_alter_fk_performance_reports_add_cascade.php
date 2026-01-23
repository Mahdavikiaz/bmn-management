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

            try { 
                $table->dropForeign(['id_asset']); 
            } catch (\Throwable $e) {

            }
            try { 
                $table->dropForeign(['id_spec']); 
            } catch (\Throwable $e) {

            }

            $table->foreign('id_asset')
                ->references('id_asset')
                ->on('assets')
                ->onDelete('cascade');

            $table->foreign('id_spec')
                ->references('id_spec')
                ->on('assets_specifications')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_reports', function (Blueprint $table) {

            try { 
                $table->dropForeign(['id_asset']); 
            } catch (\Throwable $e) {

            }
            
            try { 
                $table->dropForeign(['id_spec']); 
            } catch (\Throwable $e) {
                
            }

            $table->foreign('id_asset')
                ->references('id_asset')
                ->on('assets');

            $table->foreign('id_spec')
                ->references('id_spec')
                ->on('assets_specifications');
        });
    }
};
