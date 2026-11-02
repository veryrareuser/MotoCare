<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class CheckInBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $booking = $this->route('booking');

        return $booking instanceof Booking
            && $this->user()->can('checkIn', $booking);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'odometer_in' => ['required', 'integer', 'min:0', 'max:9999999'],
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
            'odometer_in.required' => 'Odometer masuk wajib diisi.',
            'odometer_in.integer' => 'Odometer masuk harus berupa angka.',
            'odometer_in.min' => 'Odometer masuk tidak boleh negatif.',
            'odometer_in.max' => 'Odometer masuk terlalu besar.',
        ];
    }
}
