<?php

namespace App\Models;

use Database\Factories\MotorcycleCompatibilityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sparepart_id', 'brand', 'model', 'year_from', 'year_to'])]
class MotorcycleCompatibility extends Model
{
    /** @use HasFactory<MotorcycleCompatibilityFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year_from' => 'integer',
            'year_to' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Sparepart, $this>
     */
    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }

    public function displayName(): string
    {
        $range = match (true) {
            $this->year_from !== null && $this->year_to !== null => $this->year_from.'–'.$this->year_to,
            $this->year_from !== null => 'sejak '.$this->year_from,
            $this->year_to !== null => 'sampai '.$this->year_to,
            default => null,
        };

        return trim($this->brand.' '.$this->model.($range ? ' ('.$range.')' : ''));
    }
}
