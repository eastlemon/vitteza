<?php

namespace App\Console\Commands;

use App\Models\MailDomain;
use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Provisions mailboxes for existing users (backfill for users created
 * without one, e.g. before the mailbox flow existed or via seeders).
 *
 * One password per user for both the system and the mailbox:
 * bcrypt for the app ('hashed' cast), SHA512-CRYPT for Dovecot.
 * Plaintext is printed once to the console and never stored.
 */
class MailProvision extends Command
{
    /**
     * usernames are optional; without them every active user with a username
     * and no mailbox is provisioned. --quota sets the per-mailbox quota, MB.
     */
    protected $signature = 'mail:provision {--user=* : Username(s); default: all active users without a mailbox} {--quota=2048 : Mailbox quota, MB}';

    protected $description = 'Provision mailboxes for existing users (same password for system and mail)';

    public function handle(): int
    {
        $quota = (int) $this->option('quota');
        $usernames = $this->option('user');

        $query = User::query()
            ->where('is_active', true)
            ->whereNotNull('username')
            ->orderBy('id');

        if ($usernames) {
            $query->whereIn('username', array_map('strtolower', $usernames));
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->info('No matching users.');

            return self::SUCCESS;
        }

        $domain = MailDomain::activeByName('vitteza.ru')
            ?? MailDomain::firstOrCreate(
                ['domain' => 'vitteza.ru'],
                ['quota_mb' => 2048, 'is_active' => true],
            );

        $rows = [];

        foreach ($users as $user) {
            $email = $user->email ?? strtolower($user->username).'@vitteza.ru';
            [$local] = explode('@', $email);

            if (Mailbox::where('user_id', $user->id)->exists()) {
                $this->line("skip: {$user->username} (mailbox already exists)");

                continue;
            }

            $plain = Str::password(12, symbols: false);

            DB::transaction(function () use ($user, $email, $local, $domain, $quota, $plain): void {
                // one password for the system and the mailbox
                $user->password = $plain;
                $user->email = $email;
                $user->save();

                Mailbox::create([
                    'user_id' => $user->id,
                    'email' => $email,
                    'password' => Mailbox::makeSha512CryptHash($plain),
                    'local_part' => $local,
                    'domain_id' => $domain->id,
                    'maildir' => Mailbox::maildirFor($email),
                    'quota_mb' => $quota,
                    'is_active' => true,
                ]);
            });

            $rows[] = [
                $user->name,
                $user->username,
                $email,
                $plain,
                $quota . ' MB',
            ];
        }

        // orphaned accounts without a username cannot get a mailbox
        $orphans = User::whereNull('username')->where('is_active', true)->pluck('id');
        if ($orphans->isNotEmpty()) {
            $this->warn('Skipped active users without a username (ids: ' . $orphans->implode(', ') . ')');
        }

        if ($rows === []) {
            $this->info('Nothing to provision.');

            return self::SUCCESS;
        }

        $this->table(['Name', 'Login', 'Email', 'Password (one-time)', 'Quota'], $rows);

        $this->newLine();
        $this->line('System login:  https://vitteza.ru/admin — email + the password above');
        $this->line('IMAP:          mail.vitteza.ru:993 (SSL/TLS)');
        $this->line('SMTP:          mail.vitteza.ru:465 (SSL) or 587 (STARTTLS)');
        $this->warn('Passwords are shown once; they are not stored in plaintext.');

        return self::SUCCESS;
    }
}
