<?php

namespace App\Http\Requests;

use App\Enums\MechanicAssignmentRole;
use App\Enums\UserRole;
use App\Models\ServiceOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignServiceOrderMechanicRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $serviceOrder = $this->route('serviceOrder');

        return $serviceOrder instanceof ServiceOrder
            && $this->user()->can('assignMechanic', $serviceOrder);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'mechanic_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where('role', UserRole::Mechanic->value)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'assignment_role' => ['nullable', Rule::enum(MechanicAssignmentRole::class)],
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
            'mechanic_id.required' => 'Pilih mekanik.',
            'mechanic_id.exists' => 'Pilih mekanik yang aktif.',
        ];
    }
}
