<?php

namespace App\Policies;

use App\Models\CargoTemplate;
use App\Models\User;

/**
 * Доступ к грузам (шаблонам) по компетенциям (ТЗ §8.2, §8.4).
 */
class CargoTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasCompetency('cargo.view');
    }

    public function view(User $user, CargoTemplate $tpl): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasCompetency('cargo.manage');
    }

    public function update(User $user, CargoTemplate $tpl): bool
    {
        return $user->isAdmin() || $user->hasCompetency('cargo.manage');
    }

    public function delete(User $user, CargoTemplate $tpl): bool
    {
        return $user->isAdmin() || $user->hasCompetency('cargo.manage');
    }
}
