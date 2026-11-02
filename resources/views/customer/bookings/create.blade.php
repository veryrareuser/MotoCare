@php
    $serviceData = $serviceTypes->mapWithKeys(fn ($service): array => [
        (string) $service->getKey() => [
            'price' => (float) $service->base_price,
            'duration' => (int) ($service->estimated_duration_minutes ?? 0),
        ],
    ]);

    $firstDate = $slotGroups->first()['date'] ?? null;
    $hasSlots = $slotGroups->isNotEmpty();
    $defaultCapacity = $hasSlots ? ($slotGroups->first()['slots']->first()?->capacity ?? 2) : 2;
@endphp

<x-app-layout title="Buat Booking">
    <script>
        window.motocareServiceData = @json($serviceData);
    </script>

    <div class="mx-auto max-w-6xl space-y-6">
        <a
            href="{{ route('bookings.index') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-ink"
        >
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar booking
        </a>

        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Booking baru</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Pilih motor, layanan, dan jadwal</h2>
            <p class="mt-1 max-w-2xl text-sm leading-relaxed text-muted">
                Booking otomatis terkonfirmasi selama slot masih tersedia. Anda dapat menjadwalkan ulang atau membatalkan sebelum check-in.
            </p>
        </div>

        @if ($motorcycles->isEmpty())
            <div class="flex flex-col gap-4 rounded-2xl border border-warm bg-warm/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3 text-sm text-[#8A5A00]">
                    <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                    <p>Tambahkan motor terlebih dahulu sebelum membuat booking servis.</p>
                </div>
                <a
                    href="{{ route('motorcycles.create') }}"
                    class="inline-flex shrink-0 items-center gap-2 rounded-[10px] bg-ink px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink/90"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Motor
                </a>
            </div>
        @endif

        @if (! $hasSlots)
            <div class="flex items-start gap-3 rounded-xl border border-warm bg-warm/60 px-4 py-3 text-sm text-[#8A5A00]">
                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                <p>Belum ada slot tersedia dalam {{ config('booking.booking_window_days') }} hari ke depan. Hubungi workshop untuk bantuan.</p>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('bookings.store') }}"
            class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]"
            x-data="{
                activeDate: '{{ $firstDate?->toDateString() ?? '' }}',
                services: @js(array_map('strval', old('service_type_ids', []))),
                get totalPrice() {
                    return this.services.reduce((total, id) => total + (window.motocareServiceData[id]?.price ?? 0), 0);
                },
                get totalDuration() {
                    return this.services.reduce((total, id) => total + (window.motocareServiceData[id]?.duration ?? 0), 0);
                },
                formatRupiah(value) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                }
            }"
        >
            @csrf

            <div class="space-y-5">
                <section class="min-w-0 rounded-2xl border border-line bg-white p-6 shadow-card">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-ink text-xs font-bold text-white">1</span>
                        <div>
                            <h3 class="text-base font-semibold tracking-tight text-ink">Pilih motor</h3>
                            <p class="text-xs text-muted">Satu booking untuk satu motor.</p>
                        </div>
                    </div>

                    @if ($motorcycles->isEmpty())
                        <p class="mt-4 rounded-xl border border-line bg-canvas/50 px-4 py-6 text-center text-sm text-muted">
                            Belum ada motor terdaftar.
                        </p>
                    @else
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ($motorcycles as $motorcycle)
                                <label class="cursor-pointer">
                                    <input
                                        type="radio"
                                        name="motorcycle_id"
                                        value="{{ $motorcycle->id }}"
                                        class="peer sr-only"
                                        @checked(old('motorcycle_id') == $motorcycle->id)
                                    >
                                    <span class="flex items-start gap-3 rounded-xl border border-line bg-white px-4 py-3 transition hover:bg-canvas/60 peer-checked:border-brand-accent peer-checked:bg-brand-soft/50 peer-checked:ring-2 peer-checked:ring-brand-accent/30">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-soft text-brand-dark">
                                            <x-icon name="motorcycle" class="h-4 w-4" />
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-semibold text-ink">{{ $motorcycle->displayName() }}</span>
                                            <span class="mt-0.5 block text-xs text-muted">
                                                {{ $motorcycle->license_plate ?? 'Tanpa plat' }}
                                                &middot; {{ number_format($motorcycle->odometer_current, 0, ',', '.') }} km
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('motorcycle_id')" class="mt-3" />
                    @endif
                </section>

                <section class="min-w-0 rounded-2xl border border-line bg-white p-6 shadow-card">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-ink text-xs font-bold text-white">2</span>
                        <div>
                            <h3 class="text-base font-semibold tracking-tight text-ink">Pilih layanan</h3>
                            <p class="text-xs text-muted">Bisa lebih dari satu layanan dalam satu booking.</p>
                        </div>
                    </div>

                    @if ($serviceTypes->isEmpty())
                        <p class="mt-4 rounded-xl border border-line bg-canvas/50 px-4 py-6 text-center text-sm text-muted">
                            Belum ada layanan aktif.
                        </p>
                    @else
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ($serviceTypes as $serviceType)
                                <label class="cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="service_type_ids[]"
                                        value="{{ $serviceType->id }}"
                                        x-model="services"
                                        class="peer sr-only"
                                    >
                                    <span class="flex h-full flex-col rounded-xl border border-line bg-white px-4 py-3 transition hover:bg-canvas/60 peer-checked:border-brand-accent peer-checked:bg-brand-soft/50 peer-checked:ring-2 peer-checked:ring-brand-accent/30">
                                        <span class="flex items-start justify-between gap-3">
                                            <span class="text-sm font-semibold text-ink">{{ $serviceType->name }}</span>
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-line bg-white text-transparent"
                                                :class="services.includes('{{ $serviceType->id }}') ? 'border-brand-dark bg-brand-dark text-white' : ''">
                                                <x-icon name="check" class="h-3 w-3" />
                                            </span>
                                        </span>
                                        <span class="mt-2 flex items-center gap-2 text-xs text-muted">
                                            <span class="font-semibold text-ink">{{ $serviceType->formattedPrice() }}</span>
                                            <span>&middot;</span>
                                            <span>{{ $serviceType->formattedDuration() }}</span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('service_type_ids')" class="mt-3" />
                        <x-input-error :messages="$errors->get('service_type_ids.*')" class="mt-3" />
                    @endif
                </section>

                <section class="min-w-0 rounded-2xl border border-line bg-white p-6 shadow-card">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-ink text-xs font-bold text-white">3</span>
                        <div>
                            <h3 class="text-base font-semibold tracking-tight text-ink">Pilih jadwal</h3>
                            <p class="text-xs text-muted">Kapasitas {{ $defaultCapacity }} motor per slot.</p>
                        </div>
                    </div>

                    @if (! $hasSlots)
                        <p class="mt-4 rounded-xl border border-line bg-canvas/50 px-4 py-6 text-center text-sm text-muted">
                            Tidak ada slot tersedia.
                        </p>
                    @else
                        <div class="mt-5 flex gap-2 overflow-x-auto pb-1">
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
                                class="mt-4 grid gap-2 sm:grid-cols-3 lg:grid-cols-4"
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
                    @endif
                </section>

                <section class="min-w-0 rounded-2xl border border-line bg-white p-6 shadow-card">
                    <label for="customer_note" class="text-base font-semibold tracking-tight text-ink">Catatan untuk mekanik</label>
                    <p class="mt-0.5 text-xs text-muted">Opsional. Jelaskan keluhan atau permintaan khusus.</p>

                    <textarea
                        id="customer_note"
                        name="customer_note"
                        rows="3"
                        class="mt-4 block w-full rounded-[10px] border-line bg-white px-4 py-3 text-sm text-ink shadow-sm placeholder:text-muted/60 focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/25"
                        placeholder="Contoh: rem depan terasa berdecit sejak seminggu terakhir."
                    >{{ old('customer_note') }}</textarea>
                    <x-input-error :messages="$errors->get('customer_note')" class="mt-2" />
                </section>
            </div>

            <aside class="min-w-0 xl:sticky xl:top-[108px] xl:self-start">
                <div class="rounded-2xl border border-line bg-white p-6 shadow-card">
                    <h3 class="text-base font-semibold tracking-tight text-ink">Ringkasan booking</h3>

                    <dl class="mt-5 space-y-3">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <dt class="text-muted">Layanan dipilih</dt>
                            <dd class="font-semibold text-ink"><span x-text="services.length">0</span> layanan</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <dt class="text-muted">Estimasi durasi</dt>
                            <dd class="font-semibold text-ink">
                                <span x-text="totalDuration">0</span> menit
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-line pt-3">
                            <dt class="text-sm font-semibold text-ink">Estimasi biaya</dt>
                            <dd class="text-lg font-bold tracking-tight text-ink" x-text="formatRupiah(totalPrice)">Rp 0</dd>
                        </div>
                    </dl>

                    <p class="mt-3 text-[11px] leading-relaxed text-muted">
                        Harga final dikonfirmasi saat servis dan dicatat pada invoice. Tidak ada pembayaran online.
                    </p>

                    <button
                        type="submit"
                        class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-[10px] bg-brand px-5 py-3 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent disabled:cursor-not-allowed disabled:opacity-50"
                        @disabled($motorcycles->isEmpty() || ! $hasSlots)
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Konfirmasi Booking
                    </button>

                    <a
                        href="{{ route('bookings.index') }}"
                        class="mt-2 inline-flex w-full items-center justify-center rounded-[10px] px-5 py-2.5 text-sm font-semibold text-muted transition hover:bg-canvas hover:text-ink"
                    >
                        Batal
                    </a>
                </div>
            </aside>
        </form>
    </div>
</x-app-layout>
