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
        DB::statement("
            ALTER TABLE users 
            MODIFY role ENUM('admin', 'user', 'viewer') 
            NOT NULL 
            DEFAULT 'user'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('role', 'viewer')
            ->update(['role' => 'user']);

        DB::statement("
            ALTER TABLE users 
            MODIFY role ENUM('admin', 'user') 
            NOT NULL 
            DEFAULT 'user'
        ");
    }
};
