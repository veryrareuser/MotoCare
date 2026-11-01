<?php

namespace App\Models;

use Database\Factories\WorkshopScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['day_of_week', 'open_time', 'close_time', 'slot_duration_minutes', 'capacity_per_slot', 'is_active'])]
class WorkshopSchedule extends Model
{
    /** @use HasFactory<WorkshopScheduleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'slot_duration_minutes' => 'integer',
            'capacity_per_slot' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope the query to operating days.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * ISO-8601 day label used across the UI.
     */
    public function dayLabel(): string
    {
        return match ($this->day_of_week) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
            default => 'Tidak diketahui',
        };
    }

    /**
     * Short "08:00 - 17:00" range for the schedule table.
     */
    public function timeRange(): string
    {
        return substr($this->open_time, 0, 5).' - '.substr($this->close_time, 0, 5);
    }
}
