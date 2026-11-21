<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Почтовый домен из таблицы mail_domains (ТЗ §7.3).
 * Читается Postfix (virtual_mailbox_domains) и используется
 * ресурсом «Сотрудники» при создании ящиков.
 */
class MailDomain extends Model
{
    protected $table = 'mail_domains';

    public $timestamps = true;

    protected $fillable = [
        'domain',
        'quota_mb',
        'is_active',
    ];

    protected $casts = [
        'quota_mb' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Активный домен по имени (по умолчанию vitteza.ru) или null.
     */
    public static function activeByName(string $domain): ?self
    {
        return static::where('domain', $domain)->where('is_active', true)->first();
    }

    public function mailboxes()
    {
        return $this->hasMany(Mailbox::class, 'domain_id');
    }
}
