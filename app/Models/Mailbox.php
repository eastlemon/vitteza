<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Employee mailbox in the mail_users table (read by Postfix/Dovecot).
 * The password field stores a SHA512-CRYPT hash for Dovecot — WITHOUT the 'hashed' cast,
 * we write a pre-computed hash there.
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
     * SHA512-CRYPT hash compatible with Dovecot (default_pass_scheme = SHA512-CRYPT).
     * Returns the value with the {SHA512-CRYPT} prefix, like doveadm pw.
     */
    public static function makeSha512CryptHash(string $plain): string
    {
        $salt = '$6$' . bin2hex(random_bytes(16)) . '$';
        $hash = crypt($plain, $salt);

        return '{SHA512-CRYPT}' . $hash;
    }

    /**
     * Maildir path for an email: vitteza.ru/<local-part>
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
