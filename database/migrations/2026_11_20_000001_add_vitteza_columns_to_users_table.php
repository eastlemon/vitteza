<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Расширяет стандартную таблицу users Laravel под нужды VITTEZA:
 * username, role (admin|user), is_active, competencies (JSON), last_login_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 64)->unique()->nullable()->after('name');
            $table->string('role', 16)->default('user')->after('username');
            $table->boolean('is_active')->default(true)->after('role');
            $table->json('competencies')->nullable()->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('competencies');

            $table->index(['role', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'is_active']);
            $table->dropColumn(['username', 'role', 'is_active', 'competencies', 'last_login_at']);
        });
    }
};
