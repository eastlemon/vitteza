<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

/**
 * Customer access by competencies (Spec §8.2, §8.4).
 */
class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasCompetency('customers.view');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasCompetency('customers.manage');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->isAdmin() || $user->hasCompetency('customers.manage');
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->isAdmin() || $user->hasCompetency('customers.manage');
    }
}
