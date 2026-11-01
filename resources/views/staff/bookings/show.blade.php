@php
    $canCheckIn = auth()->user()->can('checkIn', $booking);
    $canCancel = auth()->user()->can('cancel', $booking);
    $canNoShow = auth()->user()->can('markNoShow', $booking);
@endphp

<x-app-layout :title="'Antrean '.$booking->queue_number">
    <div class="mx-auto max-w-5xl space-y-6">
        <a
            href="{{ route('staff.bookings.index', ['date' => $booking->queue_date->toDateString()]) }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-ink"
        >
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke antrean
        </a>

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-brand text-base font-black tracking-tight text-ink">
                    {{ $booking->queue_number }}
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Detail antrean</p>
                    <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink">
                        {{ $booking->customer?->name ?? 'Pelanggan' }}
                    </h2>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $booking->status->tone() }}">
                            {{ $booking->status->label() }}
                        </span>
                        <span class="font-mono text-[11px] uppercase tracking-wide text-muted">{{ $booking->booking_number }}</span>
                    </div>
                </div>
            </div>

            @if ($canCheckIn || $canCancel || $canNoShow)
                <div class="flex flex-wrap items-center gap-2">
                    @if ($canCheckIn)
                        <form method="POST" action="{{ route('staff.bookings.check-in', $booking) }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-[10px] bg-brand px-4 py-2.5 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent"
                            >
                                <x-icon name="check" class="h-4 w-4" />
                                Check-in
                            </button>
                        </form>
                    @endif

                    @if ($canNoShow)
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-canvas"
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'no-show-booking')"
                        >
                            <x-icon name="alert" class="h-4 w-4" />
                            Tidak hadir
                        </button>
                    @endif

                    @if ($canCancel)
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-[10px] border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'cancel-staff-booking')"
                        >
                            Batalkan
                        </button>
                    @endif
                </div>
            @endif
        </div>

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-5">
                <section class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Jadwal</p>
                        <p class="mt-2 text-base font-semibold text-ink">
                            {{ $booking->scheduledAtLocal()->format('H:i') }} WIB
                        </p>
                        <p class="mt-1 text-sm text-muted">{{ $booking->scheduledAtLocal()->translatedFormat('d M Y') }}</p>
                    </div>

                    <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Motor</p>
                        <p class="mt-2 text-base font-semibold text-ink">{{ $booking->motorcycle?->displayName() ?? 'Motor terhapus' }}</p>
                        <p class="mt-1 text-sm text-muted">{{ $booking->motorcycle?->license_plate ?? 'Tanpa plat' }}</p>
                    </div>

                    <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Kontak</p>
                        <p class="mt-2 text-base font-semibold text-ink">{{ $booking->customer?->phone ?? '—' }}</p>
                        <p class="mt-1 truncate text-sm text-muted">{{ $booking->customer?->email }}</p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
                    <div class="flex items-center justify-between gap-3 border-b border-line px-6 py-4">
                        <div>
                            <h3 class="text-base font-semibold tracking-tight text-ink">Layanan booking</h3>
                            <p class="mt-0.5 text-xs text-muted">Harga mengikuti salinan saat booking dibuat.</p>
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
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Catatan pelanggan</p>
                        <p class="mt-2 text-sm leading-relaxed text-ink/80">{{ $booking->customer_note }}</p>
                    </section>
                @endif

                @if ($booking->status === App\Enums\BookingStatus::Cancelled && $booking->cancellation_reason)
                    <div class="flex items-start gap-3 rounded-xl border border-line bg-white px-4 py-3 text-sm text-muted">
                        <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                        <p><span class="font-semibold text-ink">Alasan pembatalan:</span> {{ $booking->cancellation_reason }}</p>
                    </div>
                @endif
            </div>

            <aside class="space-y-5">
                <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                    <h3 class="text-base font-semibold tracking-tight text-ink">Riwayat antrean</h3>
                    <p class="mt-0.5 text-xs text-muted">Status dan perubahan jadwal tercatat otomatis.</p>

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
            </aside>
        </div>

        @if ($canNoShow)
            <x-modal name="no-show-booking" maxWidth="md" focusable>
                <form method="POST" action="{{ route('staff.bookings.no-show', $booking) }}" class="p-6">
                    @csrf
                    @method('patch')

                    <h2 class="text-lg font-semibold tracking-tight text-ink">Tandai tidak hadir?</h2>
                    <p class="mt-1 text-sm leading-relaxed text-muted">
                        Booking {{ $booking->queue_number }} akan ditutup dan slotnya dilepas untuk pelanggan lain.
                    </p>

                    <div class="mt-4">
                        <x-input-label for="no_show_reason" value="Catatan (opsional)" />
                        <textarea
                            id="no_show_reason"
                            name="reason"
                            rows="3"
                            class="mt-2 block w-full rounded-[10px] border-line bg-white px-4 py-3 text-sm text-ink shadow-sm placeholder:text-muted/60 focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/25"
                            placeholder="Contoh: pelanggan tidak datang sampai slot berakhir."
                        >{{ old('reason') }}</textarea>
                        <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <x-secondary-button x-on:click="$dispatch('close')">Tutup</x-secondary-button>
                        <x-danger-button>Tandai tidak hadir</x-danger-button>
                    </div>
                </form>
            </x-modal>
        @endif

        @if ($canCancel)
            <x-modal name="cancel-staff-booking" maxWidth="md" focusable>
                <form method="POST" action="{{ route('staff.bookings.cancel', $booking) }}" class="p-6">
                    @csrf
                    @method('patch')

                    <h2 class="text-lg font-semibold tracking-tight text-ink">Batalkan booking?</h2>
                    <p class="mt-1 text-sm leading-relaxed text-muted">
                        Booking {{ $booking->queue_number }} akan dibatalkan dan slotnya dilepas.
                    </p>

                    <div class="mt-4">
                        <x-input-label for="cancel_reason" value="Alasan (opsional)" />
                        <textarea
                            id="cancel_reason"
                            name="reason"
                            rows="3"
                            class="mt-2 block w-full rounded-[10px] border-line bg-white px-4 py-3 text-sm text-ink shadow-sm placeholder:text-muted/60 focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/25"
                            placeholder="Contoh: pelanggan meminta jadwal lain."
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
</x-app-layout>
