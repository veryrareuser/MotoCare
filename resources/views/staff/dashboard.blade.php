<x-app-layout title="Dashboard">
    @php
        $firstName = explode(' ', auth()->user()->name)[0];

        $intro = match ($role) {
            \App\Enums\UserRole::Admin => 'Pantau antrean, servis berjalan, dan kesiapan tim hari ini.',
            \App\Enums\UserRole::Mechanic => 'Lihat tugas yang ditugaskan dan perbarui progres pengerjaan.',
            \App\Enums\UserRole::InventoryStaff => 'Jaga ketersediaan sparepart dan pantau pergerakan stok.',
            \App\Enums\UserRole::Cashier => 'Kelola invoice dan pembayaran pelanggan.',
            \App\Enums\UserRole::Owner => 'Pantau ringkasan operasional dan performa bengkel.',
            default => 'Selamat datang di portal operasional MotoCare.',
        };

        $heroIcon = match ($role) {
            \App\Enums\UserRole::Admin => 'clipboard',
            \App\Enums\UserRole::Mechanic => 'wrench',
            \App\Enums\UserRole::InventoryStaff => 'box',
            \App\Enums\UserRole::Cashier => 'cash',
            \App\Enums\UserRole::Owner => 'chart',
            default => 'dashboard',
        };

        $quickActions = match ($role) {
            \App\Enums\UserRole::Admin => ['Check-in booking', 'Buat servis walk-in', 'Assign mekanik'],
            \App\Enums\UserRole::Mechanic => ['Lihat tugas aktif', 'Perbarui status servis', 'Catat sparepart terpakai'],
            \App\Enums\UserRole::InventoryStaff => ['Tambah stok masuk', 'Sesuaikan stok', 'Lihat stok rendah'],
            \App\Enums\UserRole::Cashier => ['Buka invoice draft', 'Catat pembayaran', 'Cetak kwitansi'],
            \App\Enums\UserRole::Owner => ['Lihat laporan pendapatan', 'Lihat volume servis', 'Lihat stok rendah'],
            default => [],
        };
    @endphp

    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-2xl bg-ink px-6 py-8 text-white sm:px-9 sm:py-10">
            <div class="pointer-events-none absolute -right-20 -top-24 h-[340px] w-[340px] rounded-full border border-white/5"></div>
            <div class="pointer-events-none absolute -right-6 -top-10 h-[220px] w-[220px] rounded-full border border-white/5"></div>

            <div class="relative flex items-center justify-between gap-8">
                <div class="max-w-xl">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand">
                        Portal {{ $role->label() }}
                    </p>
                    <h2 class="mt-4 text-[30px] font-semibold leading-[1.2] tracking-tight text-white sm:text-[34px]">
                        Halo, {{ $firstName }}!
                    </h2>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-white/55">{{ $intro }}</p>

                    @if ($role === \App\Enums\UserRole::Owner)
                        <p class="mt-5 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-[11px] font-medium text-white/60">
                            <x-icon name="shield" class="h-3.5 w-3.5 text-brand" />
                            Mode baca saja: Anda dapat melihat data tanpa mengubahnya.
                        </p>
                    @endif
                </div>

                <div class="relative hidden h-56 w-56 shrink-0 items-center justify-center sm:flex">
                    <div class="absolute inset-0 rounded-full border border-white/5"></div>
                    <div class="absolute inset-8 rounded-full border border-white/5"></div>
                    <div class="absolute inset-16 rounded-full bg-white/[0.03]"></div>
                    <x-icon :name="$heroIcon" class="relative h-24 w-24 text-brand" />
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                @php
                    $toneClasses = match ($metric['tone']) {
                        'warm' => 'bg-warm text-[#8A5A00]',
                        'neutral' => 'bg-canvas text-ink/70',
                        default => 'bg-brand-soft text-brand-dark',
                    };
                @endphp

                <article class="flex items-start gap-4 rounded-2xl border border-line bg-white p-5 shadow-card">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $toneClasses }}">
                        <x-icon :name="$metric['icon']" class="h-6 w-6" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-muted">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-xl font-bold tracking-tight text-ink">{{ $metric['value'] }}</p>
                        <p class="mt-0.5 text-[11px] text-muted">{{ $metric['hint'] }}</p>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.35fr_1fr]">
            <article class="rounded-2xl border border-line bg-white p-6 shadow-card sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Aktivitas Hari Ini</p>
                        <h3 class="mt-2 text-lg font-semibold tracking-tight text-ink">Belum ada aktivitas</h3>
                    </div>
                    <span class="rounded-full bg-canvas px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-muted">
                        Kosong
                    </span>
                </div>

                <div class="mt-8 flex flex-col items-center justify-center rounded-xl border border-dashed border-line bg-canvas/60 px-6 py-10 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-soft text-brand-dark">
                        <x-icon :name="$heroIcon" class="h-6 w-6" />
                    </div>
                    <p class="mt-4 text-sm font-semibold text-ink">Data operasional belum tersedia</p>
                    <p class="mt-1 max-w-sm text-xs leading-relaxed text-muted">
                        Ringkasan antrean, servis, stok, dan pembayaran akan tampil di sini setelah modul terkait aktif.
                    </p>
                </div>
            </article>

            <article class="rounded-2xl border border-line bg-white p-6 shadow-card sm:p-7">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Akses Cepat</p>
                <h3 class="mt-2 text-lg font-semibold tracking-tight text-ink">Tugas utama</h3>

                <ul class="mt-5 space-y-2">
                    @foreach ($quickActions as $action)
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-line bg-canvas/50 px-4 py-3">
                            <span class="text-sm font-medium text-ink/70">{{ $action }}</span>
                            <span class="rounded-full bg-white px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-muted">Segera</span>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-4 text-[11px] leading-relaxed text-muted">
                    Tindakan akan aktif seiring modul operasional diselesaikan.
                </p>
            </article>
        </section>
    </div>
</x-app-layout>
