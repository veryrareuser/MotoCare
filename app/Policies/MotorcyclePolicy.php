<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Motorcycle;
use App\Models\User;

class MotorcyclePolicy
{
    /**
     * Determine whether the user can view any motorcycles.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Customer, UserRole::Admin, UserRole::Owner], strict: true);
    }

    /**
     * Determine whether the user can view the motorcycle.
     */
    public function view(User $user, Motorcycle $motorcycle): bool
    {
        if (in_array($user->role, [UserRole::Admin, UserRole::Owner], strict: true)) {
            return true;
        }

        return $user->role === UserRole::Customer
            && $motorcycle->user_id === $user->getKey();
    }

    /**
     * Determine whether the user can create motorcycles.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Customer, UserRole::Admin], strict: true);
    }

    /**
     * Determine whether the user can update the motorcycle.
     */
    public function update(User $user, Motorcycle $motorcycle): bool
    {
        return $user->role === UserRole::Admin || $this->owns($user, $motorcycle);
    }

    /**
     * Determine whether the user can delete the motorcycle.
     */
    public function delete(User $user, Motorcycle $motorcycle): bool
    {
        return $this->update($user, $motorcycle);
    }

    private function owns(User $user, Motorcycle $motorcycle): bool
    {
        return $user->role === UserRole::Customer
            && $motorcycle->user_id === $user->getKey();
    }
}
