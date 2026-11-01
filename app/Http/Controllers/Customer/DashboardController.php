<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return view('customer.dashboard', [
            'metrics' => [
                [
                    'label' => 'Motor Terdaftar',
                    'value' => '0',
                    'hint' => 'Maksimal 5 unit',
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
