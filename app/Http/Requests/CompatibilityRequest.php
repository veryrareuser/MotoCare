<?php

namespace App\Http\Requests;

use App\Models\Sparepart;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CompatibilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $sparepart = $this->route('sparepart');

        return $sparepart instanceof Sparepart
            && $this->user()->can('update', $sparepart);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year_from' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'year_to' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1), 'gte:year_from'],
        ];
    }

    /**
     * Normalize the motorcycle brand and model before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'brand' => Str::title(Str::squish($this->string('brand')->toString())),
            'model' => Str::squish($this->string('model')->toString()),
        ]);
    }
}
