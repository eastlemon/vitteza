<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Почтовые таблицы для Postfix/Dovecot (ТЗ §7.3).
 * Живут в одной БД с таблицами Laravel и связаны внешним ключом
 * mail_users.user_id → users.id, чтобы ресурс «Сотрудники» создавал
 * пользователя системы и ящик одной транзакцией.
 *
 * Точные DDL из ТЗ сохранены без изменений:
 *  - mail_domains: INT UNSIGNED id, domain UNIQUE, quota_mb, is_active;
 *  - mail_users:   BIGINT UNSIGNED id, user_id (SET NULL), email UNIQUE,
 *                  SHA512-CRYPT password, local_part, domain_id (FK), maildir,
 *                  quota_mb, is_active;
 *  - mail_aliases: source/destination, is_active.
 * Индексы idx_mail_users_email / idx_mail_aliases_source покрываются
 * UNIQUE-ограничениями и явными index() соответственно.
 *
 * Postfix/Dovecot подключаются пользователями postfix_ro / dovecot_ro
 * с правами SELECT (см. §7.3) — на миграции это не влияет.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_domains', function (Blueprint $table) {
            $table->increments('id');                              // INT UNSIGNED AUTO_INCREMENT
            $table->string('domain', 190)->unique();               // vitteza.ru
            $table->integer('quota_mb')->default(2048);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('mail_users', function (Blueprint $table) {
            $table->id();                                          // BIGINT UNSIGNED
            $table->unsignedBigInteger('user_id')->nullable();     // связь с users.id
            $table->string('email', 190)->unique();                // user@vitteza.ru
            $table->string('password', 255);                       // SHA512-CRYPT (для Dovecot)
            $table->string('local_part', 64);
            $table->unsignedInteger('domain_id');                  // FK → mail_domains.id
            $table->string('maildir', 190);                        // vitteza.ru/user/
            $table->integer('quota_mb')->default(2048);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('domain_id')->references('id')->on('mail_domains');

            $table->index('user_id');
            $table->index('domain_id');
        });

        Schema::create('mail_aliases', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source', 190);                         // info@vitteza.ru
            $table->string('destination', 190);                    // admin@vitteza.ru
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('source');                               // idx_mail_aliases_source
        });

        $now = now();

        // Домен по умолчанию — сразу в миграции, чтобы чистая установка
        // и RefreshDatabase в тестах всегда имели vitteza.ru (ТЗ §7.3).
        DB::table('mail_domains')->updateOrInsert(
            ['domain' => 'vitteza.ru'],
            ['quota_mb' => 2048, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        );

        // Стандартные ящики-псевдонимы домена (ТЗ §6.3) направляем админу.
        DB::table('mail_aliases')->insert([
            ['source' => 'info@vitteza.ru', 'destination' => 'admin@vitteza.ru', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['source' => 'sales@vitteza.ru', 'destination' => 'admin@vitteza.ru', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_aliases');
        Schema::dropIfExists('mail_users');
        Schema::dropIfExists('mail_domains');
    }
};
