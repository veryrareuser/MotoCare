@php
    $canManage = auth()->user()->can('recordStock', $sparepart);
    $currentType = old('movement_type', App\Enums\InventoryMovementType::In->value);
@endphp

<x-app-layout :title="$sparepart->name">
    <div class="mx-auto max-w-5xl space-y-6">
        <a
            href="{{ route('staff.spareparts.index') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-ink"
        >
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar sparepart
        </a>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Detail sparepart</p>
                <h2 class="mt-2 truncate text-2xl font-semibold tracking-tight text-ink">{{ $sparepart->name }}</h2>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="font-mono text-[11px] uppercase tracking-wide text-muted">{{ $sparepart->sku }}</span>
                    @if ($sparepart->is_active)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-brand-dark">
                            <span class="h-1.5 w-1.5 rounded-full bg-brand-dark"></span>
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-canvas px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-muted">
                            <span class="h-1.5 w-1.5 rounded-full bg-muted"></span>
                            Nonaktif
                        </span>
                    @endif
                </div>
            </div>

            @can('update', $sparepart)
                <a
                    href="{{ route('staff.spareparts.edit', $sparepart) }}"
                    class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-canvas"
                >
                    <x-icon name="wrench" class="h-4 w-4" />
                    Edit data
                </a>
            @endcan
        </div>

        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Saldo stok</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight {{ $sparepart->isLowStock() ? 'text-[#8A5A00]' : 'text-ink' }}">
                    {{ $sparepart->formattedStock() }}
                </p>
                @if ($sparepart->isLowStock())
                    <p class="mt-1 inline-flex items-center gap-1.5 text-xs font-semibold text-[#8A5A00]">
                        <x-icon name="alert" class="h-3.5 w-3.5" />
                        Pada atau di bawah minimum
                    </p>
                @else
                    <p class="mt-1 text-xs text-muted">Di atas stok minimum</p>
                @endif
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Stok minimum</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">
                    {{ number_format($sparepart->minimum_stock, 0, ',', '.') }}
                    <span class="text-sm font-medium text-muted">{{ $sparepart->unit }}</span>
                </p>
                <p class="mt-1 text-xs text-muted">Ambang peringatan stok menipis</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Harga jual</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $sparepart->formattedPrice() }}</p>
                <p class="mt-1 text-xs text-muted">Per {{ $sparepart->unit }}</p>
            </div>
        </section>

        @if ($sparepart->description)
            <section class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Deskripsi</p>
                <p class="mt-2 text-sm leading-relaxed text-ink/80">{{ $sparepart->description }}</p>
            </section>
        @endif

        <section class="rounded-2xl border border-line bg-white p-6 shadow-card sm:p-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold tracking-tight text-ink">Kompatibilitas motor</h3>
                    <p class="mt-1 text-sm leading-relaxed text-muted">
                        Model motor yang cocok memakai sparepart ini.
                    </p>
                </div>
                <span class="inline-flex w-fit items-center gap-2 rounded-full border border-line bg-canvas/60 px-3 py-1.5 text-xs font-semibold text-muted">
                    <x-icon name="motorcycle" class="h-4 w-4 text-ink/60" />
                    {{ $compatibilities->count() }} model
                </span>
            </div>

            @if ($compatibilities->isNotEmpty())
                <ul class="mt-5 flex flex-wrap gap-2">
                    @foreach ($compatibilities as $compatibility)
                        <li class="inline-flex items-center gap-2 rounded-full border border-line bg-canvas/50 py-1.5 pl-3.5 pr-1.5 text-xs font-semibold text-ink">
                            {{ $compatibility->displayName() }}

                            @if ($canManage)
                                <form method="POST" action="{{ route('staff.spareparts.compatibilities.destroy', [$sparepart, $compatibility]) }}">
                                    @csrf
                                    @method('delete')
                                    <button
                                        type="submit"
                                        class="flex h-6 w-6 items-center justify-center rounded-full text-muted transition hover:bg-white hover:text-red-600"
                                        title="Hapus kompatibilitas"
                                        aria-label="Hapus kompatibilitas {{ $compatibility->displayName() }}"
                                    >
                                        <x-icon name="x" class="h-3.5 w-3.5" />
                                    </button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-5 text-sm text-muted">Belum ada data kompatibilitas.</p>
            @endif

            @if ($canManage)
                <form method="POST" action="{{ route('staff.spareparts.compatibilities.store', $sparepart) }}" class="mt-6 space-y-4 border-t border-line pt-5">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <x-input-label for="brand" value="Merek" />
                            <x-text-input
                                id="brand"
                                name="brand"
                                type="text"
                                class="mt-2 block w-full"
                                :value="old('brand')"
                                placeholder="Honda"
                                autocomplete="off"
                            />
                            <x-input-error :messages="$errors->get('brand')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="model" value="Model" />
                            <x-text-input
                                id="model"
                                name="model"
                                type="text"
                                class="mt-2 block w-full"
                                :value="old('model')"
                                placeholder="Vario 160"
                                autocomplete="off"
                            />
                            <x-input-error :messages="$errors->get('model')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="year_from" value="Tahun dari" />
                            <x-text-input
                                id="year_from"
                                name="year_from"
                                type="number"
                                min="1900"
                                max="{{ now()->year + 1 }}"
                                class="mt-2 block w-full"
                                :value="old('year_from')"
                                placeholder="Opsional"
                                autocomplete="off"
                            />
                            <x-input-error :messages="$errors->get('year_from')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="year_to" value="Tahun sampai" />
                            <x-text-input
                                id="year_to"
                                name="year_to"
                                type="number"
                                min="1900"
                                max="{{ now()->year + 1 }}"
                                class="mt-2 block w-full"
                                :value="old('year_to')"
                                placeholder="Opsional"
                                autocomplete="off"
                            />
                            <x-input-error :messages="$errors->get('year_to')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>Tambah kompatibilitas</x-primary-button>
                    </div>
                </form>
            @endif
        </section>

        @if ($canManage)
            <section class="rounded-2xl border border-line bg-white p-6 shadow-card sm:p-8">
                <h3 class="text-base font-semibold tracking-tight text-ink">Catat pergerakan stok</h3>
                <p class="mt-1 text-sm leading-relaxed text-muted">
                    Gunakan stok masuk untuk barang datang dan penyesuaian untuk mengoreksi selisih hitung.
                </p>

                <form
                    method="POST"
                    action="{{ route('staff.spareparts.movements.store', $sparepart) }}"
                    class="mt-5 space-y-5"
                    x-data="{ type: '{{ $currentType }}' }"
                >
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="movement_type" value="Jenis pergerakan" />
                            <x-select-input id="movement_type" name="movement_type" class="mt-2 block w-full" x-model="type" required>
                                @foreach (App\Enums\InventoryMovementType::manualCases() as $type)
                                    <option value="{{ $type->value }}" @selected($currentType === $type->value)>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </x-select-input>
                            <x-input-error :messages="$errors->get('movement_type')" class="mt-2" />
                        </div>

                        <div x-show="type === 'adjustment'" x-cloak>
                            <x-input-label for="direction" value="Arah penyesuaian" />
                            <x-select-input id="direction" name="direction" class="mt-2 block w-full">
                                <option value="increase" @selected(old('direction') === 'increase')>Menambah stok</option>
                                <option value="decrease" @selected(old('direction') === 'decrease')>Mengurangi stok</option>
                            </x-select-input>
                            <x-input-error :messages="$errors->get('direction')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="quantity" value="Jumlah" />
                            <x-text-input
                                id="quantity"
                                name="quantity"
                                type="number"
                                min="1"
                                step="1"
                                class="mt-2 block w-full"
                                :value="old('quantity')"
                                placeholder="10"
                                required
                                autocomplete="off"
                            />
                            <p class="mt-1.5 text-xs text-muted">Dalam satuan {{ $sparepart->unit }}.</p>
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="notes" value="Catatan (opsional)" />
                            <x-text-input
                                id="notes"
                                name="notes"
                                type="text"
                                class="mt-2 block w-full"
                                :value="old('notes')"
                                placeholder="Contoh: kiriman supplier"
                                autocomplete="off"
                            />
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center gap-3 border-t border-line pt-5">
                        <x-primary-button>Simpan pergerakan</x-primary-button>
                        <p class="text-xs text-muted">Saldo diperbarui otomatis dan tercatat di riwayat.</p>
                    </div>
                </form>
            </section>
        @endif

        <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
            <div class="border-b border-line px-6 py-4">
                <h3 class="text-base font-semibold tracking-tight text-ink">Riwayat stok</h3>
                <p class="mt-0.5 text-xs text-muted">50 pergerakan terakhir. Riwayat tidak dapat diubah.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left">
                    <thead class="border-b border-line bg-canvas/60">
                        <tr class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
                            <th class="px-6 py-3 font-bold">Waktu</th>
                            <th class="px-6 py-3 font-bold">Jenis</th>
                            <th class="px-6 py-3 font-bold">Perubahan</th>
                            <th class="px-6 py-3 font-bold">Saldo</th>
                            <th class="px-6 py-3 font-bold">Dicatat oleh</th>
                            <th class="px-6 py-3 font-bold">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($movements as $movement)
                            <tr class="transition hover:bg-canvas/40">
                                <td class="whitespace-nowrap px-6 py-3.5 text-xs text-muted">
                                    {{ $movement->created_at?->timezone(config('app.display_timezone'))->translatedFormat('d M Y H:i') }}
                                </td>

                                <td class="px-6 py-3.5">
                                    @php
                                        $tone = match ($movement->movement_type) {
                                            App\Enums\InventoryMovementType::In => 'bg-brand-soft text-brand-dark',
                                            App\Enums\InventoryMovementType::Out => 'bg-canvas text-ink/70',
                                            App\Enums\InventoryMovementType::Adjustment => 'bg-warm text-[#8A5A00]',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $tone }}">
                                        {{ $movement->movement_type->label() }}
                                    </span>
                                </td>

                                <td class="px-6 py-3.5 text-sm font-semibold {{ $movement->isIncrease() ? 'text-brand-dark' : 'text-red-600' }}">
                                    {{ $movement->formattedDelta() }} {{ $sparepart->unit }}
                                </td>

                                <td class="px-6 py-3.5 text-sm text-ink">
                                    {{ number_format((float) $movement->quantity_after, 0, ',', '.') }} {{ $sparepart->unit }}
                                </td>

                                <td class="px-6 py-3.5 text-sm text-ink/80">
                                    {{ $movement->performedBy?->name ?? 'Sistem' }}
                                </td>

                                <td class="px-6 py-3.5 text-xs text-muted">
                                    {{ $movement->notes ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-muted">
                                    Belum ada pergerakan stok untuk sparepart ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
