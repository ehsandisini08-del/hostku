<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->isAdmin() || $invoice->user_id === $user->id;
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->isAdmin();
    }
}
