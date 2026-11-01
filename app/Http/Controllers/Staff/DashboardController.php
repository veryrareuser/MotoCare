<?php

namespace App\Http\Controllers\Staff;

use App\Enums\UserRole;
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
        $role = $request->user()->role;

        return view('staff.dashboard', [
            'role' => $role,
            'metrics' => $this->metricsFor($role),
        ]);
    }

    /**
     * @return list<array{label: string, value: string, hint: string, icon: string, tone: string}>
     */
    private function metricsFor(UserRole $role): array
    {
        return match ($role) {
            UserRole::Admin => [
                ['label' => 'Antrean Hari Ini', 'value' => '0', 'hint' => 'Belum ada antrean', 'icon' => 'clipboard', 'tone' => 'brand'],
                ['label' => 'Servis Berjalan', 'value' => '0', 'hint' => 'Tidak ada servis aktif', 'icon' => 'wrench', 'tone' => 'neutral'],
                ['label' => 'Menunggu Check-in', 'value' => '0', 'hint' => 'Belum ada check-in', 'icon' => 'clock', 'tone' => 'warm'],
                ['label' => 'Stok Rendah', 'value' => '0', 'hint' => 'Semua stok aman', 'icon' => 'box', 'tone' => 'neutral'],
            ],
            UserRole::Mechanic => [
                ['label' => 'Tugas Aktif', 'value' => '0', 'hint' => 'Belum ada penugasan', 'icon' => 'wrench', 'tone' => 'brand'],
                ['label' => 'Menunggu Inspeksi', 'value' => '0', 'hint' => 'Belum ada kendaraan', 'icon' => 'clipboard', 'tone' => 'warm'],
                ['label' => 'Selesai Hari Ini', 'value' => '0', 'hint' => 'Belum ada pekerjaan selesai', 'icon' => 'check', 'tone' => 'neutral'],
            ],
            UserRole::InventoryStaff => [
                ['label' => 'Total Sparepart', 'value' => '0', 'hint' => 'Katalog masih kosong', 'icon' => 'box', 'tone' => 'brand'],
                ['label' => 'Stok Rendah', 'value' => '0', 'hint' => 'Semua stok aman', 'icon' => 'alert', 'tone' => 'warm'],
                ['label' => 'Pergerakan Hari Ini', 'value' => '0', 'hint' => 'Belum ada transaksi stok', 'icon' => 'history', 'tone' => 'neutral'],
            ],
            UserRole::Cashier => [
                ['label' => 'Invoice Belum Lunas', 'value' => '0', 'hint' => 'Tidak ada tagihan terbuka', 'icon' => 'cash', 'tone' => 'warm'],
                ['label' => 'Pembayaran Hari Ini', 'value' => '0', 'hint' => 'Belum ada pembayaran', 'icon' => 'clipboard', 'tone' => 'neutral'],
                ['label' => 'Pendapatan Hari Ini', 'value' => 'Rp 0', 'hint' => 'Belum ada transaksi', 'icon' => 'chart', 'tone' => 'brand'],
            ],
            UserRole::Owner => [
                ['label' => 'Pendapatan Hari Ini', 'value' => 'Rp 0', 'hint' => 'Belum ada transaksi', 'icon' => 'chart', 'tone' => 'brand'],
                ['label' => 'Servis Hari Ini', 'value' => '0', 'hint' => 'Belum ada servis', 'icon' => 'wrench', 'tone' => 'neutral'],
                ['label' => 'Pelanggan Baru', 'value' => '0', 'hint' => 'Belum ada pelanggan baru', 'icon' => 'users', 'tone' => 'warm'],
                ['label' => 'Stok Rendah', 'value' => '0', 'hint' => 'Semua stok aman', 'icon' => 'box', 'tone' => 'neutral'],
            ],
            default => [],
        };
    }
}
