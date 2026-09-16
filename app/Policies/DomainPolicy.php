<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\User;

class DomainPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Domain $domain): bool
    {
        return $user->isAdmin() || $domain->service?->user_id === $user->id;
    }

    public function update(User $user, Domain $domain): bool
    {
        return $user->isAdmin();
    }
}
