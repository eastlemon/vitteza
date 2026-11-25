<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;

/**
 * Trip access by competencies (Spec §8.2, §8.3, §8.4).
 * Own/all trip visibility is implemented via the visibleTo() scope on the Trip model
 * and getEloquentQuery() in TripResource — the policy grants "section access".
 */
class TripPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasCompetency('trips.view');
    }

    public function view(User $user, Trip $trip): bool
    {
        if ($user->isAdmin() || $user->hasCompetency('trips.view_all')) {
            return true;
        }

        return (int) $trip->logistic_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasCompetency('trips.manage');
    }

    public function update(User $user, Trip $trip): bool
    {
        if (! ($user->isAdmin() || $user->hasCompetency('trips.manage'))) {
            return false;
        }

        return $this->view($user, $trip);
    }

    public function delete(User $user, Trip $trip): bool
    {
        return $this->update($user, $trip);
    }
}
