<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can browse bookings.
     *
     * Customers only ever see their own bookings through the query scope.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        if ($user->role === UserRole::Customer) {
            return $booking->user_id === $user->getKey();
        }

        return $user->role->isStaff();
    }

    /**
     * Determine whether the user can create a booking.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::Customer;
    }

    /**
     * Determine whether the booking can still be rescheduled.
     */
    public function reschedule(User $user, Booking $booking): bool
    {
        return $user->role === UserRole::Customer
            && $booking->user_id === $user->getKey()
            && $booking->canBeChanged();
    }

    /**
     * Determine whether the booking can still be cancelled.
     *
     * Customers may cancel their own booking until check-in. Operational
     * staff may cancel any booking that has not been checked in.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->role === UserRole::Customer) {
            return $booking->user_id === $user->getKey()
                && $booking->status === BookingStatus::Confirmed;
        }

        return in_array($user->role, $this->operationalRoles(), strict: true)
            && $booking->status === BookingStatus::Confirmed;
    }

    /**
     * Determine whether the user can check the booking in.
     */
    public function checkIn(User $user, Booking $booking): bool
    {
        return in_array($user->role, $this->operationalRoles(), strict: true)
            && $booking->status === BookingStatus::Confirmed;
    }

    /**
     * Determine whether the user can mark the booking as a no-show.
     */
    public function markNoShow(User $user, Booking $booking): bool
    {
        return in_array($user->role, $this->operationalRoles(), strict: true)
            && $booking->status === BookingStatus::Confirmed;
    }

    /**
     * Roles allowed to run the front-desk queue.
     *
     * @return list<UserRole>
     */
    private function operationalRoles(): array
    {
        return [UserRole::Admin, UserRole::Mechanic, UserRole::Cashier];
    }
}
