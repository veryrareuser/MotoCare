<?php

namespace App\Http\Requests;

use App\Enums\ServiceOrderStatus;
use App\Models\ServiceOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $serviceOrder = $this->route('serviceOrder');

        return $serviceOrder instanceof ServiceOrder
            && $this->user()->can('updateStatus', $serviceOrder);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ServiceOrderStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'odometer_out' => ['nullable', 'integer', 'min:0', 'max:9999999'],
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
            'status.required' => 'Pilih status baru.',
            'odometer_out.integer' => 'Odometer keluar harus berupa angka.',
            'odometer_out.min' => 'Odometer keluar tidak boleh negatif.',
            'odometer_out.max' => 'Odometer keluar terlalu besar.',
        ];
    }
}
