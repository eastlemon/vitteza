<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\MailDomain;
use App\Models\Mailbox;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    /**
     * Creates the system user AND the mailbox in a single transaction.
     * The password is the same: for the system — bcrypt (via the model's 'hashed' cast),
     * for mail — SHA512-CRYPT (for Dovecot). Plaintext is not stored in the DB,
     * but flashed to the session for the one-time credential card.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $username = strtolower($data['username']);
        $email = $username.'@vitteza.ru';
        $plain = $data['password'];

        return DB::transaction(function () use ($data, $username, $email, $plain): Model {
            $user = User::create([
                'name' => $data['name'],
                'username' => $username,
                'email' => $email,
                'password' => $plain,
                'role' => $data['role'],
                'is_active' => true,
                'competencies' => $data['competencies'] ?? [],
            ]);

            $mailEmail = null;
            $mailPassword = null;

            if (! empty($data['create_mailbox'])) {
                $domainRow = MailDomain::activeByName('vitteza.ru');

                // Protection against DB desync (domain not seeded) instead of
                // crashing on null->id: create the domain idempotently.
                if (! $domainRow) {
                    $domainRow = MailDomain::firstOrCreate(
                        ['domain' => 'vitteza.ru'],
                        ['quota_mb' => 2048, 'is_active' => true],
                    );
                }

                $mailEmail = $email;
                [$local] = explode('@', $mailEmail);

                Mailbox::create([
                    'user_id' => $user->id,
                    'email' => $mailEmail,
                    'password' => Mailbox::makeSha512CryptHash($plain),
                    'local_part' => $local,
                    'domain_id' => $domainRow->id,
                    'maildir' => Mailbox::maildirFor($mailEmail),
                    'quota_mb' => (int) ($data['quota_mb'] ?? 2048),
                    'is_active' => true,
                ]);

                $mailPassword = $plain;
            }

            Session::flash('vitteza_creds', [
                'name' => $user->name,
                'app_url' => rtrim(config('app.url'), '/').'/admin',
                'login' => $user->email,
                'app_password' => $plain,
                'mail_email' => $mailEmail,
                'mail_password' => $mailPassword,
                'date' => now()->format('d.m.Y H:i'),
            ]);

            return $user;
        });
    }

    protected function getRedirectUrl(): string
    {
        return route('credential.card');
    }
}
