<?php

namespace App\Http\Requests;

use App\Enums\InventoryMovementType;
use App\Models\Sparepart;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $sparepart = $this->route('sparepart');

        return $sparepart instanceof Sparepart
            && $this->user()->can('recordStock', $sparepart);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'movement_type' => [
                'required',
                Rule::in(array_map(
                    fn (InventoryMovementType $type): string => $type->value,
                    InventoryMovementType::manualCases(),
                )),
            ],
            'direction' => [
                'nullable',
                'required_if:movement_type,'.InventoryMovementType::Adjustment->value,
                Rule::in(['increase', 'decrease']),
            ],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
