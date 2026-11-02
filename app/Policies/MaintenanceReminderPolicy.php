<?php

namespace App\Policies;

use App\Enums\MaintenanceReminderStatus;
use App\Enums\UserRole;
use App\Models\MaintenanceReminder;
use App\Models\User;

class MaintenanceReminderPolicy
{
    /**
     * Determine whether the user can browse reminders.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Customer,
            UserRole::Admin,
            UserRole::Owner,
        ], strict: true);
    }

    /**
     * Determine whether the user can view a reminder.
     */
    public function view(User $user, MaintenanceReminder $maintenanceReminder): bool
    {
        if ($user->role === UserRole::Customer) {
            return $maintenanceReminder->motorcycle?->user_id === $user->getKey();
        }

        return in_array($user->role, [UserRole::Admin, UserRole::Owner], strict: true);
    }

    /**
     * Determine whether the user can create a reminder.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can edit a reminder.
     *
     * Sent reminders are already visible to the customer, so they are only
     * editable while still scheduled.
     */
    public function update(User $user, MaintenanceReminder $maintenanceReminder): bool
    {
        return $user->role === UserRole::Admin
            && $maintenanceReminder->status === MaintenanceReminderStatus::Scheduled;
    }

    /**
     * Determine whether the user can cancel a reminder.
     */
    public function cancel(User $user, MaintenanceReminder $maintenanceReminder): bool
    {
        return $user->role === UserRole::Admin
            && $maintenanceReminder->status->isOpen();
    }

    /**
     * Determine whether the user can mark a reminder as completed.
     */
    public function complete(User $user, MaintenanceReminder $maintenanceReminder): bool
    {
        return $user->role === UserRole::Admin
            && $maintenanceReminder->status->isOpen();
    }
}
