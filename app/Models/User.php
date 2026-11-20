<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'is_active',
        'competencies',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'competencies' => 'array',
            'last_login_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Доступ к Filament-панели (Filament v5 требует FilamentUser в production).
     * Все активные сотрудники могут входить; доступ к разделам регулируется
     * ролями/компетенциями через политики (ТЗ §8).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isActive();
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Доступ к компетенции. Администратору доступно всё.
     */
    public function hasCompetency(string $key): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return in_array($key, $this->competencies ?? [], true);
    }

    /**
     * Связь с почтовым ящиком (mail_users.user_id).
     * Таблица mail_users не является Eloquent-моделью по умолчанию,
     * поэтому связь описана через query к DB.
     */
    public function mailbox()
    {
        return \App\Models\Mailbox::where('user_id', $this->id)->first();
    }
}
