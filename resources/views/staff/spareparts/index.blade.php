@php($canManage = auth()->user()->can('create', App\Models\Sparepart::class))

<x-app-layout title="Sparepart">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Inventaris</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Sparepart</h2>
                <p class="mt-1 max-w-xl text-sm leading-relaxed text-muted">
                    Pantau harga jual dan saldo stok. Saldo hanya berubah melalui pencatatan stok masuk atau penyesuaian.
                </p>
            </div>

            @if ($canManage)
                <a
                    href="{{ route('staff.spareparts.create') }}"
                    class="inline-flex items-center gap-2 rounded-[10px] bg-brand px-4 py-2.5 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Sparepart
                </a>
            @endif
        </div>

        @if ($lowStockCount > 0)
            <div class="flex items-start gap-3 rounded-xl border border-warm bg-warm/60 px-4 py-3 text-sm text-[#8A5A00]">
                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                <p>
                    {{ $lowStockCount }} sparepart berada pada atau di bawah stok minimum.
                    @if (! $onlyLowStock)
                        <a href="{{ route('staff.spareparts.index', ['low_stock' => 1]) }}" class="font-semibold underline underline-offset-2">Lihat daftar</a>
                    @endif
                </p>
            </div>
        @endif

        <form method="GET" action="{{ route('staff.spareparts.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-[240px] flex-1">
                <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                <x-text-input
                    name="search"
                    type="search"
                    class="block w-full pl-10"
                    :value="$search"
                    placeholder="Cari nama atau SKU"
                />
            </div>

            <label class="inline-flex cursor-pointer items-center gap-2 rounded-[10px] border border-line bg-white px-4 py-3 text-sm font-semibold text-ink">
                <input
                    name="low_stock"
                    type="checkbox"
                    value="1"
                    class="h-4 w-4 rounded border-line text-brand-dark focus:ring-brand-accent"
                    @checked($onlyLowStock)
                >
                Stok menipis
            </label>

            <x-secondary-button type="submit">Terapkan</x-secondary-button>

            @if ($search !== '' || $onlyLowStock)
                <a href="{{ route('staff.spareparts.index') }}" class="text-sm font-semibold text-muted transition hover:text-ink">
                    Reset
                </a>
            @endif
        </form>

        <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[880px] text-left">
                    <thead class="border-b border-line bg-canvas/60">
                        <tr class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
                            <th class="px-6 py-4 font-bold">Sparepart</th>
                            <th class="px-6 py-4 font-bold">Harga jual</th>
                            <th class="px-6 py-4 font-bold">Stok</th>
                            <th class="px-6 py-4 font-bold">Status</th>
                            <th class="px-6 py-4 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($spareparts as $sparepart)
                            <tr class="transition hover:bg-canvas/40">
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-canvas text-ink/70">
                                            <x-icon name="box" class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-ink">{{ $sparepart->name }}</p>
                                            <p class="mt-0.5 font-mono text-[11px] uppercase tracking-wide text-muted">{{ $sparepart->sku }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm font-semibold text-ink">
                                    {{ $sparepart->formattedPrice() }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold {{ $sparepart->isLowStock() ? 'text-[#8A5A00]' : 'text-ink' }}">
                                            {{ $sparepart->formattedStock() }}
                                        </span>
                                        @if ($sparepart->isLowStock())
                                            <span class="rounded-full bg-warm px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-[#8A5A00]">
                                                Menipis
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 text-[11px] text-muted">Min. {{ number_format($sparepart->minimum_stock, 0, ',', '.') }} {{ $sparepart->unit }}</p>
                                </td>

                                <td class="px-6 py-4">
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
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a
                                        href="{{ route('staff.spareparts.show', $sparepart) }}"
                                        class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-3.5 py-2 text-xs font-semibold text-ink transition hover:bg-canvas"
                                    >
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-soft text-brand-dark">
                                            <x-icon name="box" class="h-6 w-6" />
                                        </div>
                                        <p class="mt-4 text-sm font-semibold text-ink">
                                            {{ $search !== '' || $onlyLowStock ? 'Tidak ada sparepart yang cocok' : 'Belum ada sparepart' }}
                                        </p>
                                        <p class="mt-1 text-sm text-muted">
                                            {{ $search !== '' || $onlyLowStock ? 'Ubah kata kunci atau filter stok menipis.' : 'Tambahkan sparepart pertama untuk mulai mencatat stok.' }}
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
