<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine whether the user can browse invoices.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, $this->viewerRoles(), strict: true);
    }

    /**
     * Determine whether the user can view an invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->role === UserRole::Customer) {
            return $invoice->serviceOrder->customer_id === $user->getKey();
        }

        return in_array($user->role, $this->staffViewerRoles(), strict: true);
    }

    /**
     * Determine whether the user can change the deposit settings.
     */
    public function updateDeposit(User $user, Invoice $invoice): bool
    {
        return $user->role === UserRole::Cashier;
    }

    /**
     * Determine whether the user can record a payment.
     */
    public function recordPayment(User $user, Invoice $invoice): bool
    {
        return $user->role === UserRole::Cashier;
    }

    /**
     * Roles that may open the invoice screens.
     *
     * @return list<UserRole>
     */
    private function viewerRoles(): array
    {
        return [
            UserRole::Customer,
            UserRole::Admin,
            UserRole::Cashier,
            UserRole::Owner,
        ];
    }

    /**
     * Staff roles that may read every invoice.
     *
     * @return list<UserRole>
     */
    private function staffViewerRoles(): array
    {
        return [
            UserRole::Admin,
            UserRole::Cashier,
            UserRole::Owner,
        ];
    }
}
