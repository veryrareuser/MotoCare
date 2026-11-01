<?php

namespace App\Http\Requests;

use App\Models\Sparepart;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SparepartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $sparepart = $this->route('sparepart');

        return $sparepart instanceof Sparepart
            ? $this->user()->can('update', $sparepart)
            : $this->user()->can('create', Sparepart::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Sparepart|string|null $sparepart */
        $sparepart = $this->route('sparepart');

        return [
            'sku' => [
                'required',
                'string',
                'max:80',
                Rule::unique('spareparts', 'sku')->ignore($sparepart),
            ],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'unit' => ['required', 'string', 'max:30'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'minimum_stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Normalize the SKU before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'sku' => Str::upper(Str::squish($this->string('sku')->toString())),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
