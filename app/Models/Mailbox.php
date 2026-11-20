<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Почтовый ящик сотрудника в таблице mail_users (читается Postfix/Dovecot).
 * Поле password хранит SHA512-CRYPT хеш для Dovecot — БЕЗ 'hashed'-каста,
 * мы записываем туда заранее вычисленный хеш.
 */
class Mailbox extends Model
{
    protected $table = 'mail_users';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'email',
        'password',
        'local_part',
        'domain_id',
        'maildir',
        'quota_mb',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'quota_mb' => 'integer',
    ];

    /**
     * SHA512-CRYPT хеш, совместимый с Dovecot (default_pass_scheme = SHA512-CRYPT).
     * Возвращает значение с префиксом {SHA512-CRYPT}, как у doveadm pw.
     */
    public static function makeSha512CryptHash(string $plain): string
    {
        $salt = '$6$' . bin2hex(random_bytes(16)) . '$';
        $hash = crypt($plain, $salt);

        return '{SHA512-CRYPT}' . $hash;
    }

    /**
     * Путь maildir для email: vitteza.ru/<local-part>
     */
    public static function maildirFor(string $email): string
    {
        $parts = array_pad(explode('@', $email, 2), 2, '');
        [$local, $domain] = $parts;

        return $domain . '/' . $local;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
