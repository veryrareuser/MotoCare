<?php

namespace App\Http\Requests;

use App\Models\ServiceOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceOrderServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $serviceOrder = $this->route('serviceOrder');

        return $serviceOrder instanceof ServiceOrder
            && $this->user()->can('recordService', $serviceOrder);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'service_type_id' => [
                'required',
                'integer',
                Rule::exists('service_types', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:999'],
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
            'service_type_id.required' => 'Pilih layanan.',
            'service_type_id.exists' => 'Layanan yang dipilih sudah tidak tersedia.',
            'quantity.required' => 'Jumlah layanan wajib diisi.',
            'quantity.gt' => 'Jumlah layanan harus lebih dari nol.',
        ];
    }
}
