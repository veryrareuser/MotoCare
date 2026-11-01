<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the staff account list.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Owner], strict: true);
    }

    /**
     * Determine whether the user can provision staff accounts.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can reactivate a staff account.
     */
    public function activate(User $user, User $account): bool
    {
        return $this->manages($user, $account) && ! $account->is_active;
    }

    /**
     * Determine whether the user can deactivate a staff account.
     */
    public function deactivate(User $user, User $account): bool
    {
        return $this->manages($user, $account)
            && $account->is_active
            && $user->getKey() !== $account->getKey();
    }

    private function manages(User $user, User $account): bool
    {
        return $user->role === UserRole::Admin
            && $account->isStaff()
            && $account->role !== UserRole::Owner;
    }
}
