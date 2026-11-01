@php
    $canReschedule = auth()->user()->can('reschedule', $booking);
    $canCancel = auth()->user()->can('cancel', $booking);
    $firstDate = $slotGroups->first()['date'] ?? null;
@endphp

<x-app-layout :title="'Booking '.$booking->queue_number">
    <div class="mx-auto max-w-5xl space-y-6">
        <a
            href="{{ route('bookings.index') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-ink"
        >
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar booking
        </a>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-brand text-base font-black tracking-tight text-ink">
                    {{ $booking->queue_number }}
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Detail booking</p>
                    <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink">
                        {{ $booking->motorcycle?->displayName() ?? 'Motor terhapus' }}
                    </h2>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $booking->status->tone() }}">
                            {{ $booking->status->label() }}
                        </span>
                        <span class="font-mono text-[11px] uppercase tracking-wide text-muted">{{ $booking->booking_number }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-5">
                <section class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Jadwal servis</p>
                        <p class="mt-2 text-base font-semibold text-ink">
                            {{ $booking->scheduledAtLocal()->translatedFormat('l, d M Y') }}
                        </p>
                        <p class="mt-1 text-sm text-muted">
                            {{ $booking->scheduledAtLocal()->format('H:i') }} WIB
                            @if ($booking->slot)
                                &middot; {{ $booking->slot->displayRange() }}
                            @endif
                        </p>
                    </div>

                    <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Motor</p>
                        <p class="mt-2 text-base font-semibold text-ink">{{ $booking->motorcycle?->displayName() ?? 'Motor terhapus' }}</p>
                        <p class="mt-1 text-sm text-muted">{{ $booking->motorcycle?->license_plate ?? 'Tanpa plat' }}</p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
                    <div class="flex items-center justify-between gap-3 border-b border-line px-6 py-4">
                        <div>
                            <h3 class="text-base font-semibold tracking-tight text-ink">Layanan</h3>
                            <p class="mt-0.5 text-xs text-muted">Harga dan durasi tersimpan saat booking dibuat.</p>
                        </div>
                        <span class="rounded-full border border-line bg-canvas/60 px-3 py-1 text-xs font-semibold text-muted">
                            {{ $booking->services->count() }} layanan
                        </span>
                    </div>

                    <div class="divide-y divide-line">
                        @foreach ($booking->services as $service)
                            <div class="flex items-center justify-between gap-4 px-6 py-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $service->serviceType?->name ?? 'Layanan' }}</p>
                                    <p class="mt-0.5 text-xs text-muted">{{ $service->formattedDuration() }}</p>
                                </div>
                                <p class="shrink-0 text-sm font-semibold text-ink">{{ $service->formattedPrice() }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between gap-4 border-t border-line bg-canvas/40 px-6 py-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Estimasi total</p>
                            <p class="mt-0.5 text-xs text-muted">{{ $booking->formattedTotalDuration() }}</p>
                        </div>
                        <p class="text-lg font-bold tracking-tight text-ink">{{ $booking->formattedTotalPrice() }}</p>
                    </div>
                </section>

                @if ($booking->customer_note)
                    <section class="rounded-2xl border border-line bg-white p-5 shadow-card">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Catatan Anda</p>
                        <p class="mt-2 text-sm leading-relaxed text-ink/80">{{ $booking->customer_note }}</p>
                    </section>
                @endif

                @if ($booking->status === App\Enums\BookingStatus::Cancelled && $booking->cancellation_reason)
                    <div class="flex items-start gap-3 rounded-xl border border-line bg-white px-4 py-3 text-sm text-muted">
                        <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                        <p><span class="font-semibold text-ink">Alasan pembatalan:</span> {{ $booking->cancellation_reason }}</p>
                    </div>
                @endif

                @if ($canReschedule && $slotGroups->isNotEmpty())
                    <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                        <h3 class="text-base font-semibold tracking-tight text-ink">Jadwalkan ulang</h3>
                        <p class="mt-1 text-sm leading-relaxed text-muted">
                            Pilih slot baru. Slot lama otomatis dilepas dan riwayat perubahan tersimpan.
                        </p>

                        <form
                            method="POST"
                            action="{{ route('bookings.reschedule', $booking) }}"
                            class="mt-5"
                            x-data="{ activeDate: '{{ $firstDate?->toDateString() ?? '' }}' }"
                        >
                            @csrf
                            @method('patch')

                            <div class="flex gap-2 overflow-x-auto pb-1">
                                @foreach ($slotGroups as $group)
                                    <button
                                        type="button"
                                        @click="activeDate = '{{ $group['date']->toDateString() }}'"
                                        class="shrink-0 rounded-xl border px-3.5 py-2.5 text-center transition"
                                        :class="activeDate === '{{ $group['date']->toDateString() }}'
                                            ? 'border-ink bg-ink text-white'
                                            : 'border-line bg-white text-ink hover:bg-canvas'"
                                    >
                                        <span class="block text-[10px] font-bold uppercase tracking-[0.14em] opacity-70">
                                            {{ $group['date']->translatedFormat('D') }}
                                        </span>
                                        <span class="mt-0.5 block text-sm font-bold">
                                            {{ $group['date']->translatedFormat('d M') }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>

                            @foreach ($slotGroups as $group)
                                <div
                                    x-show="activeDate === '{{ $group['date']->toDateString() }}'"
                                    x-cloak
                                    class="mt-4 grid gap-2 sm:grid-cols-3"
                                >
                                    @foreach ($group['slots'] as $slot)
                                        <label class="cursor-pointer">
                                            <input
                                                type="radio"
                                                name="booking_slot_id"
                                                value="{{ $slot->id }}"
                                                class="peer sr-only"
                                                @checked(old('booking_slot_id') == $slot->id)
                                            >
                                            <span class="flex flex-col items-center rounded-xl border border-line bg-white px-3 py-2.5 text-center transition hover:bg-canvas/60 peer-checked:border-brand-accent peer-checked:bg-brand-soft/60 peer-checked:ring-2 peer-checked:ring-brand-accent/30">
                                                <span class="text-sm font-semibold text-ink">{{ $slot->displayRange() }}</span>
                                                <span class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-muted">
                                                    Sisa {{ $slot->remainingCapacity() }} slot
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach

                            <x-input-error :messages="$errors->get('booking_slot_id')" class="mt-3" />

                            <div class="mt-4 flex justify-end border-t border-line pt-4">
                                <x-primary-button>Simpan jadwal baru</x-primary-button>
                            </div>
                        </form>
                    </section>
                @endif

                @if ($canCancel)
                    <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-base font-semibold tracking-tight text-ink">Batalkan booking</h3>
                                <p class="mt-1 max-w-lg text-sm leading-relaxed text-muted">
                                    Pembatalan hanya dapat dilakukan sebelum check-in. Slot akan dilepas untuk pelanggan lain.
                                </p>
                            </div>

                            <button
                                type="button"
                                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-[10px] border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                                x-data=""
                                x-on:click.prevent="$dispatch('open-modal', 'cancel-booking')"
                            >
                                Batalkan booking
                            </button>
                        </div>
                    </section>

                    <x-modal name="cancel-booking" maxWidth="md" focusable>
                        <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="p-6">
                            @csrf
                            @method('patch')

                            <h2 class="text-lg font-semibold tracking-tight text-ink">Batalkan booking ini?</h2>
                            <p class="mt-1 text-sm leading-relaxed text-muted">
                                Booking {{ $booking->queue_number }} pada
                                {{ $booking->scheduledAtLocal()->translatedFormat('d M Y H:i') }} akan dibatalkan.
                            </p>

                            <div class="mt-4">
                                <x-input-label for="reason" value="Alasan (opsional)" />
                                <textarea
                                    id="reason"
                                    name="reason"
                                    rows="3"
                                    class="mt-2 block w-full rounded-[10px] border-line bg-white px-4 py-3 text-sm text-ink shadow-sm placeholder:text-muted/60 focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/25"
                                    placeholder="Contoh: ada keperluan mendadak."
                                >{{ old('reason') }}</textarea>
                                <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <x-secondary-button x-on:click="$dispatch('close')">Tutup</x-secondary-button>
                                <x-danger-button>Batalkan booking</x-danger-button>
                            </div>
                        </form>
                    </x-modal>
                @endif
            </div>

            <aside class="space-y-5">
                <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                    <h3 class="text-base font-semibold tracking-tight text-ink">Riwayat booking</h3>
                    <p class="mt-0.5 text-xs text-muted">Setiap perubahan status dan jadwal dicatat.</p>

                    <ol class="mt-5 space-y-4">
                        @forelse ($booking->statusLogs->sortBy('created_at') as $log)
                            <li class="relative border-l border-line pl-4 last:border-transparent">
                                <span class="absolute -left-[5px] top-1 h-2.5 w-2.5 rounded-full bg-brand-dark"></span>
                                <p class="text-xs font-semibold text-ink">{{ $log->summary() }}</p>
                                <p class="mt-1 text-[11px] text-muted">
                                    {{ $log->created_at?->timezone(config('app.display_timezone'))->translatedFormat('d M Y H:i') }}
                                    &middot; {{ $log->changedBy?->name ?? 'Sistem' }}
                                </p>
                                @if ($log->reason && ! $log->old_scheduled_at)
                                    <p class="mt-1 text-[11px] italic text-muted">{{ $log->reason }}</p>
                                @endif
                            </li>
                        @empty
                            <li class="text-sm text-muted">Belum ada riwayat.</li>
                        @endforelse
                    </ol>
                </section>

                <section class="rounded-2xl border border-line bg-canvas/60 p-5">
                    <div class="flex items-start gap-3">
                        <x-icon name="clipboard" class="mt-0.5 h-4 w-4 shrink-0 text-brand-dark" />
                        <div>
                            <p class="text-sm font-semibold text-ink">Tunjukkan nomor antrean</p>
                            <p class="mt-1 text-xs leading-relaxed text-muted">
                                Datang sesuai jadwal dan tunjukkan {{ $booking->queue_number }} ke staf workshop untuk check-in.
                            </p>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
