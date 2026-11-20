<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
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
     * Создаёт пользователя системы И почтовый ящик в одной транзакции.
     * Пароль один и тот же: для системы — bcrypt (через 'hashed'-каст модели),
     * для почты — SHA512-CRYPT (для Dovecot). Plaintext в БД не хранится,
     * но flashed в session для одноразовой карточки учётных данных.
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
                $domainRow = DB::table('mail_domains')
                    ->where('domain', 'vitteza.ru')
                    ->where('is_active', 1)
                    ->first();

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
