<?php

namespace App\Actions\Motorcycle;

use App\Models\Motorcycle;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateMotorcycle
{
    /**
     * Register a motorcycle for a customer while enforcing the active-unit limit.
     *
     * The owner row is locked so two concurrent requests for the same customer
     * cannot both read a count below the limit and insert a sixth motorcycle.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws ValidationException
     */
    public function handle(User $customer, array $attributes): Motorcycle
    {
        return DB::transaction(function () use ($customer, $attributes): Motorcycle {
            User::query()
                ->whereKey($customer->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $activeCount = $customer->motorcycles()->count();

            if ($activeCount >= Motorcycle::MAX_PER_CUSTOMER) {
                throw ValidationException::withMessages([
                    'motorcycle' => 'Anda sudah mendaftarkan '.Motorcycle::MAX_PER_CUSTOMER.' motor. Hapus salah satu motor untuk menambah yang baru.',
                ]);
            }

            return $customer->motorcycles()->create($attributes);
        });
    }
}
