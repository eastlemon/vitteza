<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mail domain from the mail_domains table (Spec §7.3).
 * Read by Postfix (virtual_mailbox_domains) and used
 * by the "Employees" resource when creating mailboxes.
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
     * Active domain by name (vitteza.ru by default) or null.
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
