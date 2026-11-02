<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceDepositRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return $invoice instanceof Invoice
            && $this->user()->can('updateDeposit', $invoice);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'deposit_required' => ['required', 'boolean'],
            'deposit_percentage' => ['nullable', 'numeric', 'min:1', 'max:100'],
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
            'deposit_required.required' => 'Pilihan deposit wajib diisi.',
            'deposit_required.boolean' => 'Pilihan deposit tidak valid.',
            'deposit_percentage.numeric' => 'Persentase deposit harus berupa angka.',
            'deposit_percentage.min' => 'Persentase deposit minimal 1%.',
            'deposit_percentage.max' => 'Persentase deposit maksimal 100%.',
        ];
    }
}
