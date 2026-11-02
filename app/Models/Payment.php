<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'invoice_id',
    'payment_reference',
    'amount',
    'payment_method',
    'payment_status',
    'received_by',
    'paid_at',
    'notes',
])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by')->withTrashed();
    }

    public function methodLabel(): string
    {
        return $this->payment_method->label();
    }

    public function formattedAmount(): string
    {
        return 'Rp '.number_format((float) $this->amount, 0, ',', '.');
    }

    public function formattedPaidAt(): string
    {
        return $this->paid_at === null
            ? '—'
            : $this->paid_at->timezone(config('app.display_timezone'))->translatedFormat('d M Y H:i');
    }
}
