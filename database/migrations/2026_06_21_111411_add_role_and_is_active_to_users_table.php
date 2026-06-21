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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'kasir', 'audit', 'manager', 'staff_toko'])->default('staff_toko')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
            $table->index('role', 'idx_users_role');
            $table->index('email', 'idx_users_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_email');
            $table->dropColumn(['role', 'is_active']);
        });
    }
};