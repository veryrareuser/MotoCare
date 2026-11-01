<?php

namespace App\Http\Requests;

use App\Models\ServiceType;
use Illuminate\Foundation\Http\FormRequest;

class ServiceTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $serviceType = $this->route('service_type');

        return $serviceType instanceof ServiceType
            ? $this->user()->can('update', $serviceType)
            : $this->user()->can('create', ServiceType::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
