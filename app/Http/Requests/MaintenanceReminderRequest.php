<?php

namespace App\Http\Requests;

use App\Models\MaintenanceReminder;
use App\Models\Motorcycle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MaintenanceReminderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $reminder = $this->route('reminder');

        return $reminder instanceof MaintenanceReminder
            ? $this->user()->can('update', $reminder)
            : $this->user()->can('create', MaintenanceReminder::class);
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
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date', 'required_without:due_odometer'],
            'due_odometer' => ['nullable', 'integer', 'min:0', 'max:9999999', 'required_without:due_date'],
        ];
    }

    /**
     * Reject a due odometer that is already behind the motorcycle.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $motorcycleId = $this->input('motorcycle_id');
            $dueOdometer = $this->input('due_odometer');

            if (! is_numeric($motorcycleId) || ! is_numeric($dueOdometer)) {
                return;
            }

            $motorcycle = Motorcycle::query()->whereKey($motorcycleId)->first();

            if ($motorcycle !== null && (int) $dueOdometer < $motorcycle->odometer_current) {
                $validator->errors()->add(
                    'due_odometer',
                    'Odometer jatuh tempo tidak boleh lebih kecil dari odometer motor saat ini ('
                        .number_format($motorcycle->odometer_current, 0, ',', '.')
                        .' km).',
                );
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'due_date' => $this->filled('due_date') ? $this->input('due_date') : null,
            'due_odometer' => $this->filled('due_odometer') ? $this->input('due_odometer') : null,
        ]);
    }

    /**
     * Get custom validation messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'motorcycle_id.required' => 'Pilih motor yang akan diingatkan.',
            'motorcycle_id.exists' => 'Motor yang dipilih tidak tersedia.',
            'title.required' => 'Judul pengingat wajib diisi.',
            'title.max' => 'Judul pengingat maksimal 180 karakter.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'due_date.date' => 'Tanggal jatuh tempo tidak valid.',
            'due_date.required_without' => 'Isi tanggal jatuh tempo atau odometer jatuh tempo.',
            'due_odometer.integer' => 'Odometer jatuh tempo harus berupa angka.',
            'due_odometer.min' => 'Odometer jatuh tempo tidak boleh negatif.',
            'due_odometer.max' => 'Odometer jatuh tempo terlalu besar.',
            'due_odometer.required_without' => 'Isi tanggal jatuh tempo atau odometer jatuh tempo.',
        ];
    }
}
