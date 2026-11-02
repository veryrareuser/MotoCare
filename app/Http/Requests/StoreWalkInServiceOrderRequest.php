<?php

namespace App\Http\Requests;

use App\Models\ServiceOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWalkInServiceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', ServiceOrder::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'motorcycle_id' => [
                'required',
                'integer',
                Rule::exists('motorcycles', 'id')->whereNull('deleted_at'),
            ],
            'service_type_ids' => ['required', 'array', 'min:1'],
            'service_type_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('service_types', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'odometer_in' => ['required', 'integer', 'min:0', 'max:9999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'motorcycle_id.required' => 'Pilih motor pelanggan.',
            'motorcycle_id.exists' => 'Motor yang dipilih tidak tersedia.',
            'service_type_ids.required' => 'Pilih minimal satu layanan.',
            'service_type_ids.min' => 'Pilih minimal satu layanan.',
            'odometer_in.required' => 'Odometer masuk wajib diisi.',
            'odometer_in.integer' => 'Odometer masuk harus berupa angka.',
            'odometer_in.min' => 'Odometer masuk tidak boleh negatif.',
            'odometer_in.max' => 'Odometer masuk terlalu besar.',
        ];
    }
}
