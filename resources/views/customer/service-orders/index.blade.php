<x-app-layout title="Riwayat Servis">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Perawatan</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Riwayat Servis</h2>
                <p class="mt-1 max-w-xl text-sm leading-relaxed text-muted">
                    Semua servis yang sudah selesai atau sudah diambil, lengkap dengan rincian biaya.
                </p>
            </div>

            <a
                href="{{ route('tracking.index') }}"
                class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-canvas"
            >
                <x-icon name="wrench" class="h-4 w-4" />
                Servis aktif
            </a>
        </div>

        @if ($serviceOrders->isEmpty())
            <section class="rounded-2xl border border-line bg-white p-8 shadow-card sm:p-12">
                <div class="mx-auto flex max-w-md flex-col items-center text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-soft text-brand-dark">
                        <x-icon name="clock" class="h-8 w-8" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-ink">Belum ada riwayat servis</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">
                        Riwayat akan muncul setelah servis pertama Anda selesai dikerjakan.
                    </p>
                    <a
                        href="{{ route('bookings.create') }}"
                        class="mt-6 inline-flex items-center gap-2 rounded-[10px] bg-brand px-5 py-3 text-sm font-bold text-ink transition hover:bg-brand-accent"
                    >
                        <x-icon name="plus" class="h-4 w-4" />
                        Buat Booking
                    </a>
                </div>
            </section>
        @else
            <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left">
                        <thead class="border-b border-line bg-canvas/60">
                            <tr class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
                                <th class="px-6 py-4 font-bold">Tanggal selesai</th>
                                <th class="px-6 py-4 font-bold">Motor</th>
                                <th class="px-6 py-4 font-bold">Layanan</th>
                                <th class="px-6 py-4 font-bold">Total</th>
                                <th class="px-6 py-4 font-bold">Invoice</th>
                                <th class="px-6 py-4 text-right font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($serviceOrders as $serviceOrder)
                                @php
                                    $motorcycle = $serviceOrder->motorcycle;
                                    $invoice = $serviceOrder->invoice;
                                    $serviceNames = $serviceOrder->services
                                        ->map(fn ($service) => $service->description)
                                        ->filter()
                                        ->take(2)
                                        ->implode(', ');
                                @endphp

                                <tr class="transition hover:bg-canvas/40">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-ink">
                                            {{ $serviceOrder->completed_at?->timezone(config('app.display_timezone'))->translatedFormat('d M Y') ?? '—' }}
                                        </p>
                                        <p class="mt-0.5 font-mono text-[11px] text-muted">{{ $serviceOrder->work_order_number }}</p>
                                    </td>

                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-ink">{{ $motorcycle?->displayName() ?? 'Motor terhapus' }}</p>
                                        <p class="mt-0.5 text-xs text-muted">{{ $motorcycle?->license_plate ?? 'Tanpa plat' }}</p>
                                    </td>

                                    <td class="px-6 py-4">
                                        <p class="max-w-xs text-sm text-ink">{{ $serviceNames ?: 'Tanpa layanan tercatat' }}</p>
                                        <p class="mt-0.5 text-xs text-muted">
                                            {{ $serviceOrder->services->count() }} layanan &middot; {{ $serviceOrder->parts->count() }} sparepart
                                        </p>
                                    </td>

                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-ink">{{ $serviceOrder->formattedTotalAmount() }}</p>
                                        <p class="mt-0.5 text-xs text-muted">Odometer {{ $serviceOrder->formattedOdometerOut() }}</p>
                                    </td>

                                    <td class="px-6 py-4">
                                        @if ($invoice)
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $invoice->payment_status->tone() }}">
                                                {{ $invoice->payment_status->label() }}
                                            </span>
                                            <p class="mt-1 text-xs text-muted">Sisa {{ $invoice->formattedRemaining() }}</p>
                                        @else
                                            <span class="text-xs text-muted">Tidak ada invoice</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <a
                                            href="{{ route('service-history.show', $serviceOrder) }}"
                                            class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-3.5 py-2 text-xs font-semibold text-ink transition hover:bg-canvas"
                                        >
                                            Detail
                                            <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
