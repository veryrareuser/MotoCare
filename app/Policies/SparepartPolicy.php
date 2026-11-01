<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Sparepart;
use App\Models\User;

class SparepartPolicy
{
    /**
     * Determine whether the user can browse the sparepart catalog.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a sparepart.
     */
    public function view(User $user, Sparepart $sparepart): bool
    {
        return true;
    }

    /**
     * Determine whether the user can add spareparts to the catalog.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::InventoryStaff;
    }

    /**
     * Determine whether the user can change a sparepart.
     */
    public function update(User $user, Sparepart $sparepart): bool
    {
        return $user->role === UserRole::InventoryStaff;
    }

    /**
     * Determine whether the user can retire a sparepart.
     */
    public function delete(User $user, Sparepart $sparepart): bool
    {
        return $user->role === UserRole::InventoryStaff;
    }

    /**
     * Determine whether the user can post stock movements.
     */
    public function recordStock(User $user, Sparepart $sparepart): bool
    {
        return $user->role === UserRole::InventoryStaff;
    }
}
