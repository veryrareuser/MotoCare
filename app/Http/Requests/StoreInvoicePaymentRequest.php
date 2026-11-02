<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoicePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return $invoice instanceof Invoice
            && $this->user()->can('recordPayment', $invoice);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'notes' => ['nullable', 'string', 'max:500'],
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
            'amount.required' => 'Jumlah pembayaran wajib diisi.',
            'amount.numeric' => 'Jumlah pembayaran harus berupa angka.',
            'amount.min' => 'Jumlah pembayaran harus lebih dari nol.',
            'amount.max' => 'Jumlah pembayaran terlalu besar.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.enum' => 'Metode pembayaran tidak didukung.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
