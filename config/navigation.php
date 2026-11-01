<?php

return [

    'customer' => [
        ['section' => 'Menu', 'label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard'],
        ['label' => 'Kendaraan Saya', 'icon' => 'motorcycle'],
        ['label' => 'Booking Servis', 'icon' => 'calendar'],
        ['label' => 'Tracking Servis', 'icon' => 'history'],
        ['label' => 'Katalog Sparepart', 'icon' => 'box'],
        ['label' => 'Riwayat Servis', 'icon' => 'clock'],
        ['label' => 'Pengingat', 'icon' => 'bell'],
        ['label' => 'Profil', 'route' => 'profile.edit', 'icon' => 'user'],
    ],

    'admin' => [
        ['section' => 'Menu', 'label' => 'Dashboard', 'route' => 'staff.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Antrean', 'icon' => 'clipboard'],
        ['label' => 'Booking', 'icon' => 'calendar'],
        ['label' => 'Pelanggan', 'icon' => 'users'],
        ['section' => 'Operasional', 'label' => 'Servis', 'icon' => 'wrench'],
        ['label' => 'Layanan', 'icon' => 'check'],
        ['label' => 'Sparepart', 'icon' => 'box'],
        ['label' => 'Pengingat', 'icon' => 'bell'],
        ['label' => 'Profil', 'route' => 'profile.edit', 'icon' => 'user'],
    ],

    'mechanic' => [
        ['section' => 'Menu', 'label' => 'Dashboard', 'route' => 'staff.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Tugas Saya', 'icon' => 'wrench'],
        ['label' => 'Riwayat Pengerjaan', 'icon' => 'history'],
        ['label' => 'Profil', 'route' => 'profile.edit', 'icon' => 'user'],
    ],

    'inventory_staff' => [
        ['section' => 'Menu', 'label' => 'Dashboard', 'route' => 'staff.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Sparepart', 'icon' => 'box'],
        ['section' => 'Stok', 'label' => 'Stok Masuk', 'icon' => 'plus'],
        ['label' => 'Pergerakan Stok', 'icon' => 'history'],
        ['label' => 'Profil', 'route' => 'profile.edit', 'icon' => 'user'],
    ],

    'cashier' => [
        ['section' => 'Menu', 'label' => 'Dashboard', 'route' => 'staff.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Invoice', 'icon' => 'clipboard'],
        ['label' => 'Pembayaran', 'icon' => 'cash'],
        ['label' => 'Profil', 'route' => 'profile.edit', 'icon' => 'user'],
    ],

    'owner' => [
        ['section' => 'Menu', 'label' => 'Dashboard', 'route' => 'staff.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Laporan', 'icon' => 'chart'],
        ['section' => 'Data', 'label' => 'Pelanggan', 'icon' => 'users'],
        ['label' => 'Servis', 'icon' => 'wrench'],
        ['label' => 'Sparepart', 'icon' => 'box'],
        ['label' => 'Profil', 'route' => 'profile.edit', 'icon' => 'user'],
    ],

];
