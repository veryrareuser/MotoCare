<?php

namespace App\Models;

use App\Enums\MechanicAssignmentRole;
use Database\Factories\ServiceOrderMechanicFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_order_id', 'mechanic_id', 'assignment_role', 'assigned_at', 'unassigned_at'])]
class ServiceOrderMechanic extends Model
{
    /** @use HasFactory<ServiceOrderMechanicFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assignment_role' => MechanicAssignmentRole::class,
            'assigned_at' => 'datetime',
            'unassigned_at' => 'datetime',
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
    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mechanic_id')->withTrashed();
    }

    public function isActive(): bool
    {
        return $this->unassigned_at === null;
    }

    public function roleLabel(): string
    {
        return $this->assignment_role?->label() ?? 'Mekanik';
    }
}
