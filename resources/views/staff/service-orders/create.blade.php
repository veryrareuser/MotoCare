@php
    $serviceData = $serviceTypes->mapWithKeys(fn ($service): array => [
        (string) $service->getKey() => [
            'price' => (float) $service->base_price,
            'duration' => (int) ($service->estimated_duration_minutes ?? 0),
        ],
    ]);

    $motorcycleData = $customers
        ->flatMap(fn ($customer) => $customer->motorcycles->mapWithKeys(fn ($motorcycle): array => [
            (string) $motorcycle->getKey() => [
                'label' => $motorcycle->displayName().' — '.($motorcycle->license_plate ?? 'Tanpa plat'),
                'customer' => $customer->name,
                'odometer' => (int) $motorcycle->odometer_current,
            ],
        ]));
@endphp

<x-app-layout title="Work Order Walk-in">
    <script>
        window.motocareWalkIn = {
            services: @json($serviceData),
            motorcycles: @json($motorcycleData),
        };
    </script>

    <div class="mx-auto max-w-6xl space-y-6">
        <a
            href="{{ route('staff.service-orders.index') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-ink"
        >
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke work order
        </a>

        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Workshop</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Work order walk-in</h2>
            <p class="mt-1 max-w-2xl text-sm leading-relaxed text-muted">
                Buat work order untuk pelanggan yang datang tanpa booking. Pelanggan mengikuti motor yang dipilih.
            </p>
        </div>

        @if ($customers->isEmpty())
            <div class="flex items-start gap-3 rounded-xl border border-warm bg-warm/60 px-4 py-3 text-sm text-[#8A5A00]">
                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                <p>Belum ada pelanggan aktif dengan motor terdaftar. Tambahkan motor pelanggan terlebih dahulu.</p>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('staff.service-orders.store') }}"
            class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]"
            x-data="{
                motorcycle: '{{ old('motorcycle_id', '') }}',
                services: @js(array_map('strval', (array) old('service_type_ids', []))),
                get selectedMotorcycle() {
                    return window.motocareWalkIn.motorcycles[this.motorcycle] ?? null;
                },
                get totalPrice() {
                    return this.services.reduce((total, id) => total + (window.motocareWalkIn.services[id]?.price ?? 0), 0);
                },
                get totalDuration() {
                    return this.services.reduce((total, id) => total + (window.motocareWalkIn.services[id]?.duration ?? 0), 0);
                },
                formatRupiah(value) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                }
            }"
        >
            @csrf

            <div class="space-y-5">
                <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-ink text-xs font-bold text-white">1</span>
                        <div>
                            <h3 class="text-base font-semibold tracking-tight text-ink">Pilih motor pelanggan</h3>
                            <p class="text-xs text-muted">Daftar dikelompokkan berdasarkan pelanggan aktif.</p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <x-input-label for="motorcycle_id" value="Motor" />
                        <x-select-input
                            id="motorcycle_id"
                            name="motorcycle_id"
                            class="mt-2 block w-full"
                            x-model="motorcycle"
                            :disabled="$customers->isEmpty()"
                        >
                            <option value="">Pilih motor pelanggan</option>
                            @foreach ($customers as $customer)
                                <optgroup label="{{ $customer->name }}">
                                    @foreach ($customer->motorcycles as $motorcycle)
                                        <option value="{{ $motorcycle->id }}" @selected(old('motorcycle_id') == $motorcycle->id)>
                                            {{ $motorcycle->displayName() }} — {{ $motorcycle->license_plate ?? 'Tanpa plat' }}
                                            ({{ number_format($motorcycle->odometer_current, 0, ',', '.') }} km)
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </x-select-input>
                        <x-input-error :messages="$errors->get('motorcycle_id')" class="mt-2" />

                        <p class="mt-3 text-xs text-muted" x-show="selectedMotorcycle" x-cloak>
                            Pelanggan: <span class="font-semibold text-ink" x-text="selectedMotorcycle?.customer"></span>
                            &middot; Odometer terakhir
                            <span class="font-semibold text-ink" x-text="selectedMotorcycle ? new Intl.NumberFormat('id-ID').format(selectedMotorcycle.odometer) + ' km' : ''"></span>
                        </p>
                    </div>
                </section>

                <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-ink text-xs font-bold text-white">2</span>
                        <div>
                            <h3 class="text-base font-semibold tracking-tight text-ink">Layanan dikerjakan</h3>
                            <p class="text-xs text-muted">Bisa lebih dari satu layanan dalam satu work order.</p>
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

                <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-ink text-xs font-bold text-white">3</span>
                        <div>
                            <h3 class="text-base font-semibold tracking-tight text-ink">Odometer & catatan</h3>
                            <p class="text-xs text-muted">Odometer masuk tidak boleh lebih kecil dari catatan terakhir.</p>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="odometer_in" value="Odometer masuk (km)" />
                            <x-text-input
                                id="odometer_in"
                                name="odometer_in"
                                type="number"
                                min="0"
                                max="9999999"
                                class="mt-2 block w-full"
                                :value="old('odometer_in')"
                                placeholder="Contoh: 24500"
                            />
                            <x-input-error :messages="$errors->get('odometer_in')" class="mt-2" />
                        </div>

                        <div class="flex items-end">
                            <p class="text-xs leading-relaxed text-muted" x-show="selectedMotorcycle" x-cloak>
                                Minimal <span class="font-semibold text-ink" x-text="selectedMotorcycle ? new Intl.NumberFormat('id-ID').format(selectedMotorcycle.odometer) : ''"></span> km
                                berdasarkan odometer terakhir.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="notes" value="Catatan untuk mekanik (opsional)" />
                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            class="mt-2 block w-full rounded-[10px] border-line bg-white px-4 py-3 text-sm text-ink shadow-sm placeholder:text-muted/60 focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/25"
                            placeholder="Contoh: keluhan rem berdecit dan minta servis rutin."
                        >{{ old('notes') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>
                </section>
            </div>

            <aside class="xl:sticky xl:top-[108px] xl:self-start">
                <div class="rounded-2xl border border-line bg-white p-6 shadow-card">
                    <h3 class="text-base font-semibold tracking-tight text-ink">Ringkasan work order</h3>

                    <dl class="mt-5 space-y-3">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <dt class="text-muted">Motor</dt>
                            <dd class="max-w-[190px] truncate text-right font-semibold text-ink" x-text="selectedMotorcycle?.label ?? 'Belum dipilih'"></dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <dt class="text-muted">Layanan dipilih</dt>
                            <dd class="font-semibold text-ink"><span x-text="services.length">0</span> layanan</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <dt class="text-muted">Estimasi durasi</dt>
                            <dd class="font-semibold text-ink"><span x-text="totalDuration">0</span> menit</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-line pt-3">
                            <dt class="text-sm font-semibold text-ink">Estimasi biaya</dt>
                            <dd class="text-lg font-bold tracking-tight text-ink" x-text="formatRupiah(totalPrice)">Rp 0</dd>
                        </div>
                    </dl>

                    <p class="mt-3 text-[11px] leading-relaxed text-muted">
                        Sparepart ditambahkan dari halaman work order dan otomatis memotong stok.
                    </p>

                    <button
                        type="submit"
                        class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-[10px] bg-brand px-5 py-3 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent disabled:cursor-not-allowed disabled:opacity-50"
                        @disabled($customers->isEmpty() || $serviceTypes->isEmpty())
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Buat Work Order
                    </button>

                    <a
                        href="{{ route('staff.service-orders.index') }}"
                        class="mt-2 inline-flex w-full items-center justify-center rounded-[10px] px-5 py-2.5 text-sm font-semibold text-muted transition hover:bg-canvas hover:text-ink"
                    >
                        Batal
                    </a>
                </div>
            </aside>
        </form>
    </div>
</x-app-layout>
