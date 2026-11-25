<?php

namespace App\Policies;

use App\Models\Driver;
use App\Models\User;

/**
 * Driver access by competencies (Spec §8.2, §8.4).
 */
class DriverPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasCompetency('drivers.view');
    }

    public function view(User $user, Driver $driver): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasCompetency('drivers.manage');
    }

    public function update(User $user, Driver $driver): bool
    {
        return $user->isAdmin() || $user->hasCompetency('drivers.manage');
    }

    public function delete(User $user, Driver $driver): bool
    {
        return $user->isAdmin() || $user->hasCompetency('drivers.manage');
    }
}
