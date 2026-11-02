<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ServiceOrder;
use App\Models\User;

class ServiceOrderPolicy
{
    /**
     * Determine whether the user can browse work orders.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, $this->viewerRoles(), strict: true);
    }

    /**
     * Determine whether the user can view a work order.
     */
    public function view(User $user, ServiceOrder $serviceOrder): bool
    {
        return in_array($user->role, $this->viewerRoles(), strict: true);
    }

    /**
     * Determine whether the customer can read their own service history.
     */
    public function viewHistory(User $user, ServiceOrder $serviceOrder): bool
    {
        return $user->role === UserRole::Customer
            && $serviceOrder->customer_id === $user->getKey();
    }

    /**
     * Determine whether the user can open a walk-in work order.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, $this->frontDeskRoles(), strict: true);
    }

    /**
     * Determine whether the user can move the work order through its statuses.
     */
    public function updateStatus(User $user, ServiceOrder $serviceOrder): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Mechanic], strict: true);
    }

    /**
     * Determine whether the user can assign or release mechanics.
     */
    public function assignMechanic(User $user, ServiceOrder $serviceOrder): bool
    {
        return in_array($user->role, $this->frontDeskRoles(), strict: true);
    }

    /**
     * Determine whether the user can add or remove service lines.
     */
    public function recordService(User $user, ServiceOrder $serviceOrder): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Mechanic], strict: true);
    }

    /**
     * Determine whether the user can add or remove sparepart lines.
     */
    public function recordPart(User $user, ServiceOrder $serviceOrder): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Mechanic], strict: true);
    }

    /**
     * Roles that may read the workshop execution screens.
     *
     * Inventory staff work from the stock ledger and do not need work orders.
     * The owner is read-only.
     *
     * @return list<UserRole>
     */
    private function viewerRoles(): array
    {
        return [
            UserRole::Admin,
            UserRole::Mechanic,
            UserRole::Cashier,
            UserRole::Owner,
        ];
    }

    /**
     * Roles allowed to check in a booking or create a walk-in.
     *
     * @return list<UserRole>
     */
    private function frontDeskRoles(): array
    {
        return [
            UserRole::Admin,
            UserRole::Mechanic,
            UserRole::Cashier,
        ];
    }
}
