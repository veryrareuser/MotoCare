<?php

namespace App\Http\Requests;

use App\Models\Motorcycle;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MotorcycleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user instanceof User) {
            return false;
        }

        $motorcycle = $this->route('motorcycle');

        return $motorcycle instanceof Motorcycle
            ? $user->can('update', $motorcycle)
            : $user->can('create', Motorcycle::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Motorcycle|string|null $motorcycle */
        $motorcycle = $this->route('motorcycle');

        return [
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'license_plate' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('motorcycles', 'license_plate')
                    ->ignore($motorcycle)
                    ->whereNull('deleted_at'),
            ],
            'odometer_current' => ['required', 'integer', 'min:0', 'max:2000000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Normalize the license plate before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('license_plate')) {
            $this->merge([
                'license_plate' => Str::upper(Str::squish($this->string('license_plate')->toString())),
            ]);
        }

        if (! $this->filled('odometer_current')) {
            $this->merge(['odometer_current' => 0]);
        }
    }
}
