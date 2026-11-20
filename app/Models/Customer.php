<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Заказчик — организация (ТЗ §13).
 */
class Customer extends Model
{
    protected $fillable = [
        'name', 'full_legal_name', 'inn', 'kpp', 'ogrn',
        'legal_address', 'postal_address', 'account_number', 'bank_name',
        'bank_bik', 'correspondent_account', 'contact_person', 'contact_phone',
        'contact_email', 'is_active', 'comment',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(CustomerContract::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}
