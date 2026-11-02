<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Carbon\CarbonImmutable;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_number',
    'service_order_id',
    'subtotal',
    'discount_amount',
    'tax_amount',
    'total_amount',
    'deposit_required',
    'deposit_percentage',
    'deposit_amount',
    'paid_amount',
    'remaining_amount',
    'payment_status',
    'issued_at',
    'finalized_at',
])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'deposit_required' => 'boolean',
            'deposit_percentage' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'payment_status' => InvoiceStatus::class,
            'issued_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ServiceOrder, $this>
     */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /**
     * @return HasMany<InvoiceItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope the query to invoices created on the given local date.
     */
    public function scopeForDate(Builder $query, CarbonImmutable $date): Builder
    {
        $start = $date->startOfDay()->utc();
        $end = $date->addDay()->startOfDay()->utc();

        return $query->where('created_at', '>=', $start)
            ->where('created_at', '<', $end);
    }

    /**
     * Scope the query to invoices belonging to the given customer.
     */
    public function scopeForCustomer(Builder $query, User $user): Builder
    {
        return $query->whereHas(
            'serviceOrder',
            fn (Builder $query): Builder => $query->where('customer_id', $user->getKey()),
        );
    }

    /**
     * An invoice is a draft until the work order is completed.
     */
    public function isDraft(): bool
    {
        return $this->issued_at === null;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === InvoiceStatus::Paid;
    }

    public function formattedSubtotal(): string
    {
        return $this->formatAmount((float) $this->subtotal);
    }

    public function formattedDiscount(): string
    {
        return $this->formatAmount((float) $this->discount_amount);
    }

    public function formattedTax(): string
    {
        return $this->formatAmount((float) $this->tax_amount);
    }

    public function formattedTotal(): string
    {
        return $this->formatAmount((float) $this->total_amount);
    }

    public function formattedPaid(): string
    {
        return $this->formatAmount((float) $this->paid_amount);
    }

    public function formattedRemaining(): string
    {
        return $this->formatAmount((float) $this->remaining_amount);
    }

    public function formattedDepositAmount(): string
    {
        return $this->deposit_amount === null
            ? '—'
            : $this->formatAmount((float) $this->deposit_amount);
    }

    public function formattedDepositPercentage(): string
    {
        return $this->deposit_percentage === null
            ? '—'
            : rtrim(rtrim(number_format((float) $this->deposit_percentage, 2, ',', '.'), '0'), ',').'%';
    }

    public function formattedCreatedAt(): string
    {
        return $this->created_at->timezone(config('app.display_timezone'))->translatedFormat('d M Y H:i');
    }

    public function formattedIssuedAt(): string
    {
        return $this->issued_at === null
            ? 'Draft'
            : $this->issued_at->timezone(config('app.display_timezone'))->translatedFormat('d M Y H:i');
    }

    public function hasPayments(): bool
    {
        return $this->payments()->exists();
    }

    private function formatAmount(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
