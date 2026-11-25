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
     * Access to the Filament panel (Filament v5 requires FilamentUser in production).
     * All active employees can log in; access to sections is governed
     * by roles/competencies via policies (Spec §8).
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
     * Competency access. The administrator has everything.
     */
    public function hasCompetency(string $key): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return in_array($key, $this->competencies ?? [], true);
    }

    /**
     * Mailbox relation (mail_users.user_id).
     * The mail_users table is not an Eloquent model by default,
     * so the relation is described via a DB query.
     */
    public function mailbox()
    {
        return \App\Models\Mailbox::where('user_id', $this->id)->first();
    }
}
