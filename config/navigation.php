<?php

return [

    'customer' => [
        ['section' => 'Menu', 'label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard'],
        ['label' => 'Kendaraan Saya', 'route' => 'motorcycles.index', 'active' => 'motorcycles.*', 'icon' => 'motorcycle'],
        ['label' => 'Booking Servis', 'route' => 'bookings.index', 'active' => 'bookings.*', 'icon' => 'calendar'],
        ['label' => 'Tracking Servis', 'icon' => 'history'],
        ['label' => 'Katalog Sparepart', 'route' => 'spareparts.index', 'active' => 'spareparts.*', 'icon' => 'box'],
        ['label' => 'Riwayat Servis', 'icon' => 'clock'],
        ['label' => 'Pengingat', 'icon' => 'bell'],
        ['label' => 'Profil', 'route' => 'profile.edit', 'icon' => 'user'],
    ],

    'admin' => [
        ['section' => 'Menu', 'label' => 'Dashboard', 'route' => 'staff.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Antrean & Booking', 'route' => 'staff.bookings.index', 'active' => 'staff.bookings.*', 'icon' => 'clipboard'],
        ['label' => 'Pelanggan', 'icon' => 'users'],
        ['section' => 'Operasional', 'label' => 'Servis', 'icon' => 'wrench'],
        ['label' => 'Layanan', 'route' => 'staff.services.index', 'active' => 'staff.services.*', 'icon' => 'check'],
        ['label' => 'Sparepart', 'route' => 'staff.spareparts.index', 'active' => 'staff.spareparts.*', 'icon' => 'box'],
        ['label' => 'Pengingat', 'icon' => 'bell'],
        ['section' => 'Pengaturan', 'label' => 'Akun Staf', 'route' => 'staff.accounts.index', 'active' => 'staff.accounts.*', 'icon' => 'shield'],
        ['label' => 'Profil', 'route' => 'profile.edit', 'icon' => 'user'],
    ],

    'mechanic' => [
        ['section' => 'Menu', 'label' => 'Dashboard', 'route' => 'staff.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Antrean', 'route' => 'staff.bookings.index', 'active' => 'staff.bookings.*', 'icon' => 'clipboard'],
        ['label' => 'Tugas Saya', 'icon' => 'wrench'],
        ['label' => 'Riwayat Pengerjaan', 'icon' => 'history'],
        ['label' => 'Sparepart', 'route' => 'staff.spareparts.index', 'active' => 'staff.spareparts.*', 'icon' => 'box'],
        ['label' => 'Profil', 'route' => 'profile.edit', 'icon' => 'user'],
    ],

    'inventory_staff' => [
        ['section' => 'Menu', 'label' => 'Dashboard', 'route' => 'staff.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Sparepart', 'route' => 'staff.spareparts.index', 'active' => 'staff.spareparts.*', 'icon' => 'box'],
        ['section' => 'Stok', 'label' => 'Stok Masuk', 'icon' => 'plus'],
        ['label' => 'Pergerakan Stok', 'icon' => 'history'],
        ['label' => 'Profil', 'route' => 'profile.edit', 'icon' => 'user'],
    ],

    'cashier' => [
        ['section' => 'Menu', 'label' => 'Dashboard', 'route' => 'staff.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Antrean', 'route' => 'staff.bookings.index', 'active' => 'staff.bookings.*', 'icon' => 'clipboard'],
        ['label' => 'Invoice', 'icon' => 'clipboard'],
        ['label' => 'Pembayaran', 'icon' => 'cash'],
        ['label' => 'Profil', 'route' => 'profile.edit', 'icon' => 'user'],
    ],

    'owner' => [
        ['section' => 'Menu', 'label' => 'Dashboard', 'route' => 'staff.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Antrean', 'route' => 'staff.bookings.index', 'active' => 'staff.bookings.*', 'icon' => 'clipboard'],
        ['label' => 'Laporan', 'icon' => 'chart'],
        ['section' => 'Data', 'label' => 'Pelanggan', 'icon' => 'users'],
        ['label' => 'Servis', 'icon' => 'wrench'],
        ['label' => 'Layanan', 'route' => 'staff.services.index', 'active' => 'staff.services.*', 'icon' => 'check'],
        ['label' => 'Sparepart', 'route' => 'staff.spareparts.index', 'active' => 'staff.spareparts.*', 'icon' => 'box'],
        ['label' => 'Akun Staf', 'route' => 'staff.accounts.index', 'active' => 'staff.accounts.*', 'icon' => 'shield'],
        ['label' => 'Profil', 'route' => 'profile.edit', 'icon' => 'user'],
    ],

];
