<x-app-layout title="Dashboard">
    @php
        $firstName = explode(' ', auth()->user()->name)[0];
    @endphp

    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-2xl bg-ink px-6 py-8 text-white sm:px-9 sm:py-10">
            <div class="pointer-events-none absolute -right-20 -top-24 h-[340px] w-[340px] rounded-full border border-white/5"></div>
            <div class="pointer-events-none absolute -right-6 -top-10 h-[220px] w-[220px] rounded-full border border-white/5"></div>

            <div class="relative flex items-center justify-between gap-8">
                <div class="max-w-xl">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand">Selamat datang kembali</p>
                    <h2 class="mt-4 text-[30px] font-semibold leading-[1.2] tracking-tight text-white sm:text-[34px]">
                        Halo, {{ $firstName }}! Motor siap dirawat?
                    </h2>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-white/55">
                        Booking servis berikutnya dan nikmati perjalanan tanpa khawatir.
                    </p>

                    <a
                        href="{{ route('bookings.create') }}"
                        class="mt-6 inline-flex items-center gap-2 rounded-[10px] bg-brand px-5 py-3 text-sm font-bold text-ink transition hover:bg-brand-accent"
                    >
                        Booking Servis
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>

                <div class="relative hidden h-56 w-56 shrink-0 items-center justify-center sm:flex">
                    <div class="absolute inset-0 rounded-full border border-white/5"></div>
                    <div class="absolute inset-8 rounded-full border border-white/5"></div>
                    <div class="absolute inset-16 rounded-full bg-white/[0.03]"></div>
                    <x-icon name="motorcycle" class="relative h-24 w-24 text-brand" />
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
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
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Servis Sedang Berjalan</p>
                        <h3 class="mt-2 text-lg font-semibold tracking-tight text-ink">
                            {{ $activeServiceOrder ? $activeServiceOrder->motorcycle?->displayName() ?? 'Motor terhapus' : 'Belum ada servis aktif' }}
                        </h3>
                    </div>
                    <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wide {{ $activeServiceOrder?->status->tone() ?? 'bg-canvas text-muted' }}">
                        {{ $activeServiceOrder?->status->label() ?? 'Kosong' }}
                    </span>
                </div>

                @if ($activeServiceOrder)
                    <div class="mt-5 space-y-3">
                        <div class="flex flex-wrap items-center gap-2 text-xs text-muted">
                            <span class="rounded-lg bg-brand-soft px-2.5 py-1 font-mono text-[11px] font-bold text-brand-dark">
                                {{ $activeServiceOrder->work_order_number }}
                            </span>
                            <span>{{ $activeServiceOrder->motorcycle?->license_plate ?? 'Tanpa plat' }}</span>
                            <span>&middot;</span>
                            <span>Check-in {{ $activeServiceOrder->checkedInAtLocal()->translatedFormat('d M Y H:i') }}</span>
                        </div>

                        <div class="rounded-xl border border-line bg-canvas/40 px-4 py-3">
                            <p class="text-xs leading-relaxed text-muted">
                                {{ $activeServiceOrder->services->count() }} layanan dan
                                {{ $activeServiceOrder->parts->count() }} sparepart tercatat.
                                Estimasi total {{ $activeServiceOrder->formattedTotalAmount() }}.
                            </p>
                        </div>

                        <a
                            href="{{ route('service-history.show', $activeServiceOrder) }}"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-brand-dark transition hover:text-ink"
                        >
                            Lihat progres servis
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    </div>
                @else
                    <div class="mt-8 flex flex-col items-center justify-center rounded-xl border border-dashed border-line bg-canvas/60 px-6 py-10 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-soft text-brand-dark">
                            <x-icon name="wrench" class="h-6 w-6" />
                        </div>
                        <p class="mt-4 text-sm font-semibold text-ink">Belum ada servis berjalan</p>
                        <p class="mt-1 max-w-xs text-xs leading-relaxed text-muted">
                            Setelah Anda melakukan booking dan check-in, progres servis akan tampil di sini.
                        </p>
                    </div>
                @endif
            </article>

            <article class="rounded-2xl border border-line bg-white p-6 shadow-card sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Pengingat Servis</p>
                        <h3 class="mt-2 text-lg font-semibold tracking-tight text-ink">
                            {{ $dueReminders->isEmpty() ? 'Tidak ada pengingat jatuh tempo' : $dueReminders->count().' pengingat jatuh tempo' }}
                        </h3>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-warm text-[#8A5A00]">
                        <x-icon name="bell" class="h-5 w-5" />
                    </div>
                </div>

                <p class="mt-4 text-xs leading-relaxed text-muted">
                    Pengingat dibuat manual oleh staf bengkel berdasarkan tanggal atau kilometer motor Anda.
                </p>

                @if ($dueReminders->isEmpty())
                    <div class="mt-6 rounded-xl border border-dashed border-line bg-canvas/60 px-4 py-5 text-center">
                        <p class="text-xs text-muted">Tidak ada pengingat jatuh tempo saat ini.</p>
                    </div>
                @else
                    <div class="mt-5 space-y-2">
                        @foreach ($dueReminders as $reminder)
                            <a
                                href="{{ route('reminders.show', $reminder) }}"
                                class="block rounded-xl border border-line bg-canvas/40 px-4 py-3 transition hover:border-brand/40 hover:bg-canvas"
                            >
                                <p class="text-xs font-semibold text-ink">{{ $reminder->title }}</p>
                                <p class="mt-1 text-[11px] text-muted">
                                    {{ $reminder->motorcycle?->displayName() ?? 'Motor terhapus' }}
                                    &middot; jatuh tempo {{ $reminder->dueSummary() }}
                                </p>
                            </a>
                        @endforeach
                    </div>

                    <a
                        href="{{ route('reminders.index') }}"
                        class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-brand-dark transition hover:text-ink"
                    >
                        Lihat semua pengingat
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                @endif
            </article>
        </section>

        <section class="rounded-2xl border border-line bg-white p-6 shadow-card sm:p-7">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Aktivitas Terkini</p>
                    <h3 class="mt-2 text-lg font-semibold tracking-tight text-ink">Riwayat Servis Terbaru</h3>
                </div>

                @if ($recentHistory->isNotEmpty())
                    <a
                        href="{{ route('service-history.index') }}"
                        class="text-xs font-semibold text-brand-dark transition hover:text-ink"
                    >
                        Lihat semua
                    </a>
                @else
                    <span class="text-xs font-semibold text-muted">Belum ada data</span>
                @endif
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="w-full min-w-[620px] text-left">
                    <thead>
                        <tr class="border-b border-line text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
                            <th class="pb-3 pr-4 font-bold">Tanggal</th>
                            <th class="pb-3 pr-4 font-bold">Kendaraan</th>
                            <th class="pb-3 pr-4 font-bold">Layanan</th>
                            <th class="pb-3 pr-4 font-bold">Total</th>
                            <th class="pb-3 font-bold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($recentHistory as $serviceOrder)
                            @php
                                $serviceNames = $serviceOrder->services
                                    ->map(fn ($service) => $service->description)
                                    ->filter()
                                    ->take(2)
                                    ->implode(', ');
                            @endphp

                            <tr>
                                <td class="py-4 pr-4 align-top">
                                    <p class="text-xs font-semibold text-ink">
                                        {{ $serviceOrder->completed_at?->timezone(config('app.display_timezone'))->translatedFormat('d M Y') ?? '—' }}
                                    </p>
                                    <a
                                        href="{{ route('service-history.show', $serviceOrder) }}"
                                        class="mt-0.5 block font-mono text-[11px] text-brand-dark hover:underline"
                                    >
                                        {{ $serviceOrder->work_order_number }}
                                    </a>
                                </td>
                                <td class="py-4 pr-4 align-top">
                                    <p class="text-xs font-semibold text-ink">{{ $serviceOrder->motorcycle?->displayName() ?? 'Motor terhapus' }}</p>
                                    <p class="mt-0.5 text-[11px] text-muted">{{ $serviceOrder->motorcycle?->license_plate ?? 'Tanpa plat' }}</p>
                                </td>
                                <td class="py-4 pr-4 align-top">
                                    <p class="text-xs text-ink">{{ $serviceNames ?: 'Tanpa layanan tercatat' }}</p>
                                    <p class="mt-0.5 text-[11px] text-muted">
                                        {{ $serviceOrder->services->count() }} layanan &middot; {{ $serviceOrder->parts->count() }} sparepart
                                    </p>
                                </td>
                                <td class="py-4 pr-4 align-top">
                                    <p class="text-xs font-semibold text-ink">{{ $serviceOrder->formattedTotalAmount() }}</p>
                                </td>
                                <td class="py-4 align-top">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $serviceOrder->status->tone() }}">
                                        {{ $serviceOrder->status->label() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-xs text-muted">
                                    Riwayat servis akan muncul setelah servis pertama Anda selesai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
