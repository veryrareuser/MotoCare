<x-app-layout title="Booking Servis">
    @php
        $hasMotorcycles = auth()->user()->motorcycles()->exists();
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Jadwal</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Booking Servis</h2>
                <p class="mt-1 max-w-xl text-sm leading-relaxed text-muted">
                    Booking langsung terkonfirmasi bila slot masih tersedia. Nomor antrean harian diterbitkan saat booking dibuat.
                </p>
            </div>

            @if ($hasMotorcycles)
                <a
                    href="{{ route('bookings.create') }}"
                    class="inline-flex items-center gap-2 rounded-[10px] bg-brand px-4 py-2.5 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Buat Booking
                </a>
            @else
                <a
                    href="{{ route('motorcycles.create') }}"
                    class="inline-flex items-center gap-2 rounded-[10px] bg-brand px-4 py-2.5 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent"
                >
                    <x-icon name="motorcycle" class="h-4 w-4" />
                    Tambah Motor Dulu
                </a>
            @endif
        </div>

        @if ($upcoming->isEmpty() && $history->isEmpty())
            <section class="rounded-2xl border border-line bg-white p-8 shadow-card sm:p-12">
                <div class="mx-auto flex max-w-md flex-col items-center text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-soft text-brand-dark">
                        <x-icon name="calendar" class="h-8 w-8" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-ink">Belum ada booking</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">
                        Pilih motor, layanan, dan slot yang tersedia untuk membuat booking pertama Anda.
                    </p>
                    @if ($hasMotorcycles)
                        <a
                            href="{{ route('bookings.create') }}"
                            class="mt-6 inline-flex items-center gap-2 rounded-[10px] bg-brand px-5 py-3 text-sm font-bold text-ink transition hover:bg-brand-accent"
                        >
                            <x-icon name="plus" class="h-4 w-4" />
                            Buat Booking
                        </a>
                    @endif
                </div>
            </section>
        @else
            @if ($upcoming->isNotEmpty())
                <section class="space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-base font-semibold tracking-tight text-ink">Booking mendatang</h3>
                        <span class="rounded-full border border-line bg-white px-3 py-1 text-xs font-semibold text-muted">
                            {{ $upcoming->count() }} booking
                        </span>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        @foreach ($upcoming as $booking)
                            <article class="flex flex-col rounded-2xl border border-line bg-white p-6 shadow-card">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand text-sm font-black tracking-tight text-ink">
                                            {{ $booking->queue_number }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-ink">{{ $booking->motorcycle?->displayName() ?? 'Motor terhapus' }}</p>
                                            <p class="mt-0.5 text-xs text-muted">{{ $booking->motorcycle?->license_plate ?? 'Tanpa plat' }}</p>
                                        </div>
                                    </div>

                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $booking->status->tone() }}">
                                        {{ $booking->status->label() }}
                                    </span>
                                </div>

                                <div class="mt-5 rounded-xl border border-line bg-canvas/50 px-4 py-3">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Jadwal</p>
                                    <p class="mt-1 text-sm font-semibold text-ink">
                                        {{ $booking->scheduledAtLocal()->translatedFormat('l, d M Y') }}
                                    </p>
                                    <p class="text-xs text-muted">
                                        {{ $booking->scheduledAtLocal()->format('H:i') }} WIB
                                        @if ($booking->slot)
                                            &middot; {{ $booking->slot->displayRange() }}
                                        @endif
                                    </p>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-1.5">
                                    @foreach ($booking->services as $service)
                                        <span class="rounded-full border border-line bg-white px-2.5 py-1 text-[11px] font-semibold text-ink/80">
                                            {{ $service->serviceType?->name ?? 'Layanan' }}
                                        </span>
                                    @endforeach
                                </div>

                                <div class="mt-auto flex items-center justify-between gap-3 border-t border-line pt-4">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Estimasi</p>
                                        <p class="text-sm font-semibold text-ink">{{ $booking->formattedTotalPrice() }}</p>
                                    </div>

                                    <a
                                        href="{{ route('bookings.show', $booking) }}"
                                        class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-3.5 py-2 text-xs font-semibold text-ink transition hover:bg-canvas"
                                    >
                                        Detail
                                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($history->isNotEmpty())
                <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
                    <div class="border-b border-line px-6 py-4">
                        <h3 class="text-base font-semibold tracking-tight text-ink">Riwayat booking</h3>
                        <p class="mt-0.5 text-xs text-muted">Booking yang dibatalkan, tidak hadir, atau sudah menjadi servis.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="border-b border-line bg-canvas/60">
                                <tr class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
                                    <th class="px-6 py-3 font-bold">Antrean</th>
                                    <th class="px-6 py-3 font-bold">Jadwal</th>
                                    <th class="px-6 py-3 font-bold">Motor</th>
                                    <th class="px-6 py-3 font-bold">Status</th>
                                    <th class="px-6 py-3 text-right font-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($history as $booking)
                                    <tr class="transition hover:bg-canvas/40">
                                        <td class="px-6 py-3.5">
                                            <span class="font-mono text-xs font-bold text-ink">{{ $booking->queue_number }}</span>
                                        </td>
                                        <td class="px-6 py-3.5 text-xs text-muted">
                                            {{ $booking->scheduledAtLocal()->translatedFormat('d M Y, H:i') }}
                                        </td>
                                        <td class="px-6 py-3.5 text-sm text-ink/80">
                                            {{ $booking->motorcycle?->displayName() ?? 'Motor terhapus' }}
                                        </td>
                                        <td class="px-6 py-3.5">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $booking->status->tone() }}">
                                                {{ $booking->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5 text-right">
                                            <a
                                                href="{{ route('bookings.show', $booking) }}"
                                                class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-3 py-1.5 text-xs font-semibold text-ink transition hover:bg-canvas"
                                            >
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        @endif
    </div>
</x-app-layout>
