<x-app-layout title="Katalog Sparepart">
    <div class="space-y-6">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Katalog</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Katalog Sparepart</h2>
            <p class="mt-1 max-w-2xl text-sm leading-relaxed text-muted">
                Lihat ketersediaan dan harga sparepart. Pemesanan online belum tersedia; hubungi workshop untuk pemasangan.
            </p>
        </div>

        <form method="GET" action="{{ route('spareparts.index') }}" class="flex flex-wrap items-center gap-3">
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

            <x-secondary-button type="submit">Cari</x-secondary-button>

            @if ($search !== '')
                <a href="{{ route('spareparts.index') }}" class="text-sm font-semibold text-muted transition hover:text-ink">
                    Reset
                </a>
            @endif
        </form>

        @if ($spareparts->isEmpty())
            <section class="rounded-2xl border border-line bg-white p-8 shadow-card sm:p-12">
                <div class="mx-auto flex max-w-md flex-col items-center text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-soft text-brand-dark">
                        <x-icon name="box" class="h-8 w-8" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-ink">
                        {{ $search !== '' ? 'Sparepart tidak ditemukan' : 'Katalog masih kosong' }}
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">
                        {{ $search !== '' ? 'Coba kata kunci lain atau hubungi workshop untuk ketersediaan barang.' : 'Sparepart akan tampil di sini setelah ditambahkan oleh staf inventaris.' }}
                    </p>
                </div>
            </section>
        @else
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($spareparts as $sparepart)
                    <article class="flex flex-col rounded-2xl border border-line bg-white p-6 shadow-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-canvas text-ink/70">
                                    <x-icon name="box" class="h-5 w-5" />
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-base font-semibold leading-snug tracking-tight text-ink">{{ $sparepart->name }}</h3>
                                    <p class="mt-0.5 font-mono text-[11px] uppercase tracking-wide text-muted">{{ $sparepart->sku }}</p>
                                </div>
                            </div>

                            @if ((float) $sparepart->stock_quantity > 0)
                                <span class="shrink-0 rounded-full bg-brand-soft px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-brand-dark">
                                    Tersedia
                                </span>
                            @else
                                <span class="shrink-0 rounded-full bg-canvas px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-muted">
                                    Habis
                                </span>
                            @endif
                        </div>

                        @if ($sparepart->description)
                            <p class="mt-4 line-clamp-2 text-xs leading-relaxed text-muted">{{ $sparepart->description }}</p>
                        @endif

                        <dl class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-line bg-canvas/50 px-4 py-3">
                                <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Harga</dt>
                                <dd class="mt-1 text-sm font-semibold text-ink">{{ $sparepart->formattedPrice() }}</dd>
                            </div>
                            <div class="rounded-xl border border-line bg-canvas/50 px-4 py-3">
                                <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Stok</dt>
                                <dd class="mt-1 text-sm font-semibold {{ (float) $sparepart->stock_quantity > 0 ? 'text-ink' : 'text-muted' }}">
                                    {{ $sparepart->formattedStock() }}
                                </dd>
                            </div>
                        </dl>
                    </article>
                @endforeach
            </section>

            <div class="flex items-start gap-3 rounded-xl border border-line bg-white px-4 py-3 text-sm text-muted">
                <x-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-brand-dark" />
                <p>Harga dapat berubah. Harga final dikonfirmasi saat servis dan tercatat pada invoice.</p>
            </div>
        @endif
    </div>
</x-app-layout>
