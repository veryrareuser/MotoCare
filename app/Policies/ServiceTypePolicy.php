<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ServiceType;
use App\Models\User;

class ServiceTypePolicy
{
    /**
     * Determine whether the user can browse the service catalog.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a service type.
     */
    public function view(User $user, ServiceType $serviceType): bool
    {
        return true;
    }

    /**
     * Determine whether the user can add services to the catalog.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can change a service type.
     */
    public function update(User $user, ServiceType $serviceType): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can retire a service type.
     */
    public function delete(User $user, ServiceType $serviceType): bool
    {
        return $user->role === UserRole::Admin;
    }
}
