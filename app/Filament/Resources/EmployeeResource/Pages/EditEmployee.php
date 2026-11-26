<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\Mailbox;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    /**
     * Saves employee changes. If a password is filled — updates the password
     * of the system (bcrypt) and of the mailbox (SHA512-CRYPT) and builds
     * the credential card.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $plain = $data['password'] ?? null;
        unset($data['password'], $data['create_mailbox'], $data['quota_mb'], $data['derived_email']);

        $record->fill($data);
        if ($plain) {
            $record->password = $plain;
        }
        $record->save();

        if ($plain) {
            $mail = Mailbox::where('user_id', $record->id)->first();
            if ($mail) {
                $mail->password = Mailbox::makeSha512CryptHash($plain);
                $mail->save();
            }

            Session::flash('vitteza_creds', [
                'name' => $record->name,
                'app_url' => rtrim(config('app.url'), '/').'/admin',
                'login' => $record->email,
                'app_password' => $plain,
                'mail_email' => $mail?->email,
                'mail_password' => $mail ? $plain : null,
                'date' => now()->format('d.m.Y H:i'),
            ]);
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        if (session()->has('vitteza_creds')) {
            return route('credential.card');
        }

        return static::getResource()::getUrl('index');
    }
}
