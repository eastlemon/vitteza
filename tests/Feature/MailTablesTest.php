<?php

namespace Tests\Feature;

use App\Models\MailDomain;
use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Проверяет почтовый контур (ТЗ §7): наличие таблиц mail_domains /
 * mail_users / mail_aliases, их структуру (внешние ключи, уникальность)
 * и создание ящика с SHA512-CRYPT-хешем, совместимым с Dovecot.
 * Работает на sqlite :memory: (phpunit.xml), без боевой базы.
 */
class MailTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_tables_exist_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('mail_domains'));
        $this->assertTrue(Schema::hasTable('mail_users'));
        $this->assertTrue(Schema::hasTable('mail_aliases'));

        foreach (['id', 'domain', 'quota_mb', 'is_active'] as $col) {
            $this->assertTrue(Schema::hasColumn('mail_domains', $col), "mail_domains.$col missing");
        }
        foreach (['id', 'user_id', 'email', 'password', 'local_part', 'domain_id', 'maildir', 'quota_mb', 'is_active'] as $col) {
            $this->assertTrue(Schema::hasColumn('mail_users', $col), "mail_users.$col missing");
        }
        foreach (['id', 'source', 'destination', 'is_active'] as $col) {
            $this->assertTrue(Schema::hasColumn('mail_aliases', $col), "mail_aliases.$col missing");
        }
    }

    public function test_default_aliases_seeded(): void
    {
        $this->assertDatabaseHas('mail_aliases', ['source' => 'info@vitteza.ru', 'destination' => 'admin@vitteza.ru']);
        $this->assertDatabaseHas('mail_aliases', ['source' => 'sales@vitteza.ru', 'destination' => 'admin@vitteza.ru']);
    }

    public function test_mailbox_creation_with_dovecot_hash(): void
    {
        $domain = MailDomain::activeByName('vitteza.ru');
        $this->assertNotNull($domain, 'vitteza.ru mail domain not seeded');

        $user = User::create([
            'name' => 'Тестов Тест Тестович',
            'username' => 'testov',
            'email' => 'testov@vitteza.ru',
            'password' => 'Secret123!',
            'role' => 'user',
            'is_active' => true,
            'competencies' => ['trips.view'],
        ]);

        $mailbox = Mailbox::create([
            'user_id' => $user->id,
            'email' => 'testov@vitteza.ru',
            'password' => Mailbox::makeSha512CryptHash('Secret123!'),
            'local_part' => 'testov',
            'domain_id' => $domain->id,
            'maildir' => Mailbox::maildirFor('testov@vitteza.ru'),
            'quota_mb' => 2048,
            'is_active' => true,
        ]);

        // Хеш SHA512-CRYPT с префиксом для Dovecot
        $this->assertStringStartsWith('{SHA512-CRYPT}$6$', $mailbox->password);
        // Пароль в открытом виде нигде не хранится
        $this->assertNotSame('Secret123!', $mailbox->password);
        // Maildir-путь по формату ТЗ §6.4: vitteza.ru/<local>/
        $this->assertSame('vitteza.ru/testov', Mailbox::maildirFor('testov@vitteza.ru'));
        // Связи
        $this->assertSame($user->id, $mailbox->user_id);
        $this->assertSame($domain->id, $mailbox->domain_id);
        $this->assertSame('testov', $mailbox->local_part);
        $this->assertTrue($mailbox->is_active);
    }

    public function test_duplicate_mailbox_email_rejected(): void
    {
        $domain = MailDomain::activeByName('vitteza.ru');
        $user = User::create([
            'name' => 'Первый Пользователь',
            'username' => 'first',
            'email' => 'first@vitteza.ru',
            'password' => 'Secret123!',
        ]);

        $mk = fn () => Mailbox::create([
            'user_id' => $user->id,
            'email' => 'first@vitteza.ru',
            'password' => Mailbox::makeSha512CryptHash('Secret123!'),
            'local_part' => 'first',
            'domain_id' => $domain->id,
            'maildir' => 'vitteza.ru/first',
            'quota_mb' => 2048,
            'is_active' => true,
        ]);
        $mk();

        $this->expectException(\Illuminate\Database\QueryException::class);
        $mk(); // UNIQUE(mail_users.email)
    }
}
