@php
    $total = (int) $counts->sum();
    $confirmed = (int) ($counts[App\Enums\BookingStatus::Confirmed->value] ?? 0);
    $checkedIn = (int) ($counts[App\Enums\BookingStatus::CheckedIn->value] ?? 0)
        + (int) ($counts[App\Enums\BookingStatus::ConvertedToService->value] ?? 0);
    $closed = (int) ($counts[App\Enums\BookingStatus::Cancelled->value] ?? 0)
        + (int) ($counts[App\Enums\BookingStatus::NoShow->value] ?? 0);
@endphp

<x-app-layout title="Antrean & Booking">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Operasional</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Antrean & Booking</h2>
                <p class="mt-1 max-w-2xl text-sm leading-relaxed text-muted">
                    Pantau booking harian, lakukan check-in, dan tandai pelanggan yang tidak hadir.
                </p>
            </div>

            <form method="GET" action="{{ route('staff.bookings.index') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label for="date" value="Tanggal antrean" />
                    <x-text-input
                        id="date"
                        name="date"
                        type="date"
                        class="mt-2 block"
                        :value="$date->toDateString()"
                    />
                </div>

                @if ($status !== null)
                    <input type="hidden" name="status" value="{{ $status->value }}">
                @endif

                @if ($search !== '')
                    <input type="hidden" name="search" value="{{ $search }}">
                @endif

                <x-secondary-button type="submit">Tampilkan</x-secondary-button>

                @if ($date->toDateString() !== now(config('app.display_timezone'))->toDateString())
                    <a href="{{ route('staff.bookings.index') }}" class="text-sm font-semibold text-muted transition hover:text-ink">
                        Hari ini
                    </a>
                @endif
            </form>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Total booking</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $total }}</p>
                <p class="mt-1 text-xs text-muted">{{ $date->translatedFormat('l, d M Y') }}</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Menunggu</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $confirmed }}</p>
                <p class="mt-1 text-xs text-muted">Belum check-in</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Sudah check-in</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $checkedIn }}</p>
                <p class="mt-1 text-xs text-muted">Termasuk yang sudah menjadi work order</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Batal / tidak hadir</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $closed }}</p>
                <p class="mt-1 text-xs text-muted">Slot sudah dilepas</p>
            </div>
        </section>

        <form method="GET" action="{{ route('staff.bookings.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">

            <div class="relative min-w-[240px] flex-1">
                <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                <x-text-input
                    name="search"
                    type="search"
                    class="block w-full pl-10"
                    :value="$search"
                    placeholder="Cari antrean, booking, pelanggan, atau plat"
                />
            </div>

            <x-select-input name="status" class="min-w-[190px]">
                <option value="">Semua status</option>
                @foreach (App\Enums\BookingStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($status === $case)>{{ $case->label() }}</option>
                @endforeach
            </x-select-input>

            <x-secondary-button type="submit">Terapkan</x-secondary-button>

            @if ($search !== '' || $status !== null)
                <a href="{{ route('staff.bookings.index', ['date' => $date->toDateString()]) }}" class="text-sm font-semibold text-muted transition hover:text-ink">
                    Reset
                </a>
            @endif
        </form>

        <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left">
                    <thead class="border-b border-line bg-canvas/60">
                        <tr class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
                            <th class="px-6 py-4 font-bold">Antrean</th>
                            <th class="px-6 py-4 font-bold">Pelanggan & motor</th>
                            <th class="px-6 py-4 font-bold">Jadwal</th>
                            <th class="px-6 py-4 font-bold">Layanan</th>
                            <th class="px-6 py-4 font-bold">Status</th>
                            <th class="px-6 py-4 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($bookings as $booking)
                            <tr class="transition hover:bg-canvas/40">
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-lg bg-brand px-2.5 py-1.5 font-mono text-xs font-bold text-ink">
                                        {{ $booking->queue_number }}
                                    </span>
                                    <p class="mt-1 font-mono text-[10px] uppercase tracking-wide text-muted">{{ $booking->booking_number }}</p>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-ink">{{ $booking->customer?->name ?? 'Pelanggan' }}</p>
                                    <p class="mt-0.5 text-xs text-muted">
                                        {{ $booking->motorcycle?->displayName() ?? 'Motor terhapus' }}
                                        &middot; {{ $booking->motorcycle?->license_plate ?? 'Tanpa plat' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-ink">
                                        {{ $booking->scheduledAtLocal()->format('H:i') }} WIB
                                    </p>
                                    <p class="mt-0.5 text-xs text-muted">
                                        {{ $booking->slot?->displayRange() ?? $booking->scheduledAtLocal()->translatedFormat('d M Y') }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm text-ink/80">{{ $booking->services->count() }} layanan</p>
                                    <p class="mt-0.5 max-w-[220px] truncate text-xs text-muted">
                                        {{ $booking->services->map(fn ($service): ?string => $service->serviceType?->name)->filter()->implode(', ') ?: '—' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $booking->status->tone() }}">
                                        {{ $booking->status->label() }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        @can('checkIn', $booking)
                                            <a
                                                href="{{ route('staff.bookings.show', $booking) }}"
                                                class="inline-flex items-center gap-1.5 rounded-[10px] bg-brand px-3 py-1.5 text-xs font-bold text-ink transition hover:bg-brand-accent"
                                            >
                                                <x-icon name="check" class="h-3.5 w-3.5" />
                                                Check-in
                                            </a>
                                        @endcan

                                        <a
                                            href="{{ route('staff.bookings.show', $booking) }}"
                                            class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-3 py-1.5 text-xs font-semibold text-ink transition hover:bg-canvas"
                                        >
                                            Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-soft text-brand-dark">
                                            <x-icon name="clipboard" class="h-6 w-6" />
                                        </div>
                                        <p class="mt-4 text-sm font-semibold text-ink">Tidak ada booking</p>
                                        <p class="mt-1 text-sm text-muted">
                                            {{ $search !== '' || $status !== null
                                                ? 'Ubah kata kunci atau filter status.'
                                                : 'Belum ada booking pada tanggal ini.' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
