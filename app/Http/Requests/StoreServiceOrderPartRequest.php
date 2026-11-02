<?php

namespace App\Http\Requests;

use App\Models\ServiceOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceOrderPartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $serviceOrder = $this->route('serviceOrder');

        return $serviceOrder instanceof ServiceOrder
            && $this->user()->can('recordPart', $serviceOrder);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'sparepart_id' => [
                'required',
                'integer',
                Rule::exists('spareparts', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'part_quantity' => ['required', 'numeric', 'gt:0', 'max:9999'],
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
            'sparepart_id.required' => 'Pilih sparepart.',
            'sparepart_id.exists' => 'Sparepart yang dipilih sudah tidak tersedia.',
            'part_quantity.required' => 'Jumlah sparepart wajib diisi.',
            'part_quantity.gt' => 'Jumlah sparepart harus lebih dari nol.',
        ];
    }
}
