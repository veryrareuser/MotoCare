<?php

namespace App\Models;

use App\Enums\MaintenanceReminderStatus;
use Carbon\CarbonImmutable;
use Database\Factories\MaintenanceReminderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'motorcycle_id',
    'created_by',
    'title',
    'description',
    'due_date',
    'due_odometer',
    'status',
    'sent_at',
])]
class MaintenanceReminder extends Model
{
    /** @use HasFactory<MaintenanceReminderFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'due_odometer' => 'integer',
            'status' => MaintenanceReminderStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Motorcycle, $this>
     */
    public function motorcycle(): BelongsTo
    {
        return $this->belongsTo(Motorcycle::class)->withTrashed();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Scope the query to reminders that have not been sent yet.
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', MaintenanceReminderStatus::Scheduled);
    }

    /**
     * Scope the query to open reminders whose due condition has passed.
     *
     * Open reminders are still scheduled or already notified but not yet
     * completed or cancelled, so the customer still needs to act on them.
     */
    public function scopeOpenDue(Builder $query, CarbonImmutable $today): Builder
    {
        return $this->applyDueCondition(
            $query->whereIn('status', [
                MaintenanceReminderStatus::Scheduled,
                MaintenanceReminderStatus::Sent,
            ]),
            $today,
        );
    }

    /**
     * Scope the query to scheduled reminders whose due condition has passed.
     *
     * A reminder is due when its date is today or earlier, or when the
     * motorcycle odometer has reached the due odometer.
     */
    public function scopeDue(Builder $query, CarbonImmutable $today): Builder
    {
        return $this->applyDueCondition(
            $query->where('status', MaintenanceReminderStatus::Scheduled),
            $today,
        );
    }

    private function applyDueCondition(Builder $query, CarbonImmutable $today): Builder
    {
        return $query->where(function (Builder $query) use ($today): void {
            $query->where('due_date', '<=', $today->toDateString())
                ->orWhereHas('motorcycle', function (Builder $query): void {
                    $query->whereNull('motorcycles.deleted_at')
                        ->whereNotNull('maintenance_reminders.due_odometer')
                        ->whereColumn('motorcycles.odometer_current', '>=', 'maintenance_reminders.due_odometer');
                });
        });
    }

    public function hasDueCondition(): bool
    {
        return $this->due_date !== null || $this->due_odometer !== null;
    }

    public function isDue(?CarbonImmutable $today = null): bool
    {
        if ($this->status !== MaintenanceReminderStatus::Scheduled) {
            return false;
        }

        $today ??= CarbonImmutable::now(config('app.display_timezone'))->startOfDay();

        $dueByDate = $this->due_date !== null
            && $this->due_date->format('Y-m-d') <= $today->toDateString();

        $dueByOdometer = $this->due_odometer !== null
            && $this->motorcycle !== null
            && $this->motorcycle->odometer_current >= $this->due_odometer;

        return $dueByDate || $dueByOdometer;
    }

    public function isOverdue(?CarbonImmutable $today = null): bool
    {
        if (! $this->status->isOpen()) {
            return false;
        }

        $today ??= CarbonImmutable::now(config('app.display_timezone'))->startOfDay();

        $dateOverdue = $this->due_date !== null
            && $this->due_date->format('Y-m-d') < $today->toDateString();

        $odometerOverdue = $this->due_odometer !== null
            && $this->motorcycle !== null
            && $this->motorcycle->odometer_current > $this->due_odometer;

        return $dateOverdue || $odometerOverdue;
    }

    public function formattedDueDate(): string
    {
        return $this->due_date === null
            ? '—'
            : $this->due_date->translatedFormat('d M Y');
    }

    public function formattedDueOdometer(): string
    {
        return $this->due_odometer === null
            ? '—'
            : number_format($this->due_odometer, 0, ',', '.').' km';
    }

    /**
     * Human-readable due condition, e.g. "tanggal 12 Nov 2026 atau odometer 16.000 km".
     */
    public function dueSummary(): string
    {
        $parts = [];

        if ($this->due_date !== null) {
            $parts[] = 'tanggal '.$this->formattedDueDate();
        }

        if ($this->due_odometer !== null) {
            $parts[] = 'odometer '.$this->formattedDueOdometer();
        }

        return $parts === []
            ? 'Belum ada patokan jatuh tempo'
            : implode(' atau ', $parts);
    }

    public function formattedSentAt(): string
    {
        return $this->sent_at === null
            ? '—'
            : $this->sent_at->timezone(config('app.display_timezone'))->translatedFormat('d M Y H:i');
    }
}
