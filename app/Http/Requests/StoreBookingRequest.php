<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Booking::class);
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
                Rule::exists('motorcycles', 'id')
                    ->where('user_id', $this->user()->getKey())
                    ->whereNull('deleted_at'),
            ],
            'booking_slot_id' => [
                'required',
                'integer',
                Rule::exists('booking_slots', 'id'),
            ],
            'service_type_ids' => ['required', 'array', 'min:1'],
            'service_type_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('service_types', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'customer_note' => ['nullable', 'string', 'max:1000'],
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
            'motorcycle_id.required' => 'Pilih motor yang akan diservis.',
            'booking_slot_id.required' => 'Pilih jadwal servis.',
            'service_type_ids.required' => 'Pilih minimal satu layanan.',
            'service_type_ids.min' => 'Pilih minimal satu layanan.',
        ];
    }
}
