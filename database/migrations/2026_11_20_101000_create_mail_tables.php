<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mail tables for Postfix/Dovecot (Spec §7.3).
 * Live in the same DB as the Laravel tables and are linked by the foreign key
 * mail_users.user_id → users.id, so that the "Employees" resource creates
 * the system user and the mailbox in one transaction.
 *
 * The exact DDL from the Spec is kept unchanged:
 *  - mail_domains: INT UNSIGNED id, domain UNIQUE, quota_mb, is_active;
 *  - mail_users:   BIGINT UNSIGNED id, user_id (SET NULL), email UNIQUE,
 *                  SHA512-CRYPT password, local_part, domain_id (FK), maildir,
 *                  quota_mb, is_active;
 *  - mail_aliases: source/destination, is_active.
 * The indexes idx_mail_users_email / idx_mail_aliases_source are covered
 * by UNIQUE constraints and explicit index() respectively.
 *
 * Postfix/Dovecot connect as postfix_ro / dovecot_ro users
 * with SELECT privileges (see §7.3) — this does not affect migrations.
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
            $table->unsignedBigInteger('user_id')->nullable();     // link to users.id
            $table->string('email', 190)->unique();                // user@vitteza.ru
            $table->string('password', 255);                       // SHA512-CRYPT (for Dovecot)
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

        // Default domain — right in the migration, so that a clean install
        // and RefreshDatabase in tests always have vitteza.ru (Spec §7.3).
        DB::table('mail_domains')->updateOrInsert(
            ['domain' => 'vitteza.ru'],
            ['quota_mb' => 2048, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        );

        // Standard domain alias mailboxes (Spec §6.3) are routed to the admin.
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
