<?php

namespace App\Enums;

enum ServiceOrderStatus: string
{
    case WaitingQueue = 'waiting_queue';
    case BeingInspected = 'being_inspected';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case PickedUp = 'picked_up';

    public function label(): string
    {
        return match ($this) {
            self::WaitingQueue => 'Menunggu Antrean',
            self::BeingInspected => 'Sedang Diperiksa',
            self::InProgress => 'Sedang Dikerjakan',
            self::Completed => 'Selesai',
            self::PickedUp => 'Sudah Diambil',
        };
    }

    /**
     * Badge classes matching the shared design language.
     */
    public function tone(): string
    {
        return match ($this) {
            self::WaitingQueue => 'bg-canvas text-muted',
            self::BeingInspected => 'bg-warm text-[#8A5A00]',
            self::InProgress => 'bg-brand-soft text-brand-dark',
            self::Completed => 'bg-brand text-ink',
            self::PickedUp => 'bg-canvas text-ink/70',
        };
    }

    /**
     * The next statuses this status may move to.
     *
     * The workflow is deliberately linear: work cannot skip inspection, be
     * completed before it starts, or be picked up before completion.
     *
     * @return list<self>
     */
    public function allowedNextStatuses(): array
    {
        return match ($this) {
            self::WaitingQueue => [self::BeingInspected],
            self::BeingInspected => [self::InProgress],
            self::InProgress => [self::Completed],
            self::Completed => [self::PickedUp],
            self::PickedUp => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedNextStatuses(), strict: true);
    }

    /**
     * Whether the work order is still open for service and part changes.
     */
    public function isLocked(): bool
    {
        return in_array($this, [self::Completed, self::PickedUp], strict: true);
    }

    /**
     * Statuses that still require workshop attention.
     *
     * @return list<self>
     */
    public static function activeCases(): array
    {
        return [self::WaitingQueue, self::BeingInspected, self::InProgress];
    }
}
