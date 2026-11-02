<?php

namespace App\Models;

use App\Enums\ServiceOrderStatus;
use Database\Factories\ServiceStatusLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_order_id', 'changed_by', 'old_status', 'new_status', 'notes'])]
class ServiceStatusLog extends Model
{
    /** @use HasFactory<ServiceStatusLogFactory> */
    use HasFactory;

    /**
     * History rows are immutable; only the creation time is recorded.
     */
    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_status' => ServiceOrderStatus::class,
            'new_status' => ServiceOrderStatus::class,
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
     * @return BelongsTo<User, $this>
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by')->withTrashed();
    }

    /**
     * Short description of what changed, used in the work order timeline.
     */
    public function summary(): string
    {
        if ($this->old_status === null) {
            return 'Work order dibuat dan masuk antrean.';
        }

        return 'Status berubah dari '.$this->old_status->label().' menjadi '.$this->new_status->label().'.';
    }
}
