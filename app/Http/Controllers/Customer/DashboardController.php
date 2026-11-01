<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Motorcycle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $motorcycleCount = $request->user()->motorcycles()->count();

        return view('customer.dashboard', [
            'metrics' => [
                [
                    'label' => 'Motor Terdaftar',
                    'value' => (string) $motorcycleCount,
                    'hint' => $motorcycleCount.' dari '.Motorcycle::MAX_PER_CUSTOMER.' slot terpakai',
                    'icon' => 'motorcycle',
                    'tone' => 'brand',
                ],
                [
                    'label' => 'Servis Aktif',
                    'value' => '0',
                    'hint' => 'Tidak ada servis berjalan',
                    'icon' => 'wrench',
                    'tone' => 'neutral',
                ],
                [
                    'label' => 'Pengingat Jatuh Tempo',
                    'value' => '0',
                    'hint' => 'Belum ada pengingat',
                    'icon' => 'bell',
                    'tone' => 'warm',
                ],
            ],
        ]);
    }
}
