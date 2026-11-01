<x-app-layout title="Kendaraan Saya">
    @php
        $count = $motorcycles->count();
        $atLimit = $count >= $limit;
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Garasi</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Kendaraan Saya</h2>
                <p class="mt-1 max-w-xl text-sm leading-relaxed text-muted">
                    Simpan data motor agar booking, servis, dan pengingat lebih cepat diproses.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-muted">
                    <x-icon name="motorcycle" class="h-4 w-4 text-ink/60" />
                    {{ $count }} / {{ $limit }} motor
                </span>

                @if ($atLimit)
                    <span class="inline-flex items-center gap-2 rounded-[10px] bg-warm px-4 py-2.5 text-sm font-semibold text-[#8A5A00]">
                        <x-icon name="alert" class="h-4 w-4" />
                        Batas tercapai
                    </span>
                @else
                    <a
                        href="{{ route('motorcycles.create') }}"
                        class="inline-flex items-center gap-2 rounded-[10px] bg-brand px-4 py-2.5 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent"
                    >
                        <x-icon name="plus" class="h-4 w-4" />
                        Tambah Motor
                    </a>
                @endif
            </div>
        </div>

        @if ($atLimit)
            <div class="flex items-start gap-3 rounded-xl border border-warm bg-warm/60 px-4 py-3 text-sm text-[#8A5A00]">
                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                <p>
                    Anda sudah mencapai batas {{ $limit }} motor. Hapus salah satu motor terlebih dahulu untuk menambah yang baru.
                </p>
            </div>
        @endif

        @if ($motorcycles->isEmpty())
            <section class="rounded-2xl border border-line bg-white p-8 shadow-card sm:p-12">
                <div class="mx-auto flex max-w-md flex-col items-center text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-soft text-brand-dark">
                        <x-icon name="motorcycle" class="h-8 w-8" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-ink">Belum ada motor terdaftar</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">
                        Tambahkan motor pertama Anda untuk mulai membuat booking servis dan melihat riwayat perawatan.
                    </p>
                    <a
                        href="{{ route('motorcycles.create') }}"
                        class="mt-6 inline-flex items-center gap-2 rounded-[10px] bg-brand px-5 py-3 text-sm font-bold text-ink transition hover:bg-brand-accent"
                    >
                        <x-icon name="plus" class="h-4 w-4" />
                        Tambah Motor
                    </a>
                </div>
            </section>
        @else
            <section class="grid gap-4 md:grid-cols-2">
                @foreach ($motorcycles as $motorcycle)
                    <article class="flex flex-col rounded-2xl border border-line bg-white p-6 shadow-card">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 items-start gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-brand-dark">
                                    <x-icon name="motorcycle" class="h-5 w-5" />
                                </div>
                                <div class="min-w-0">
                                    <h3 class="truncate text-base font-semibold tracking-tight text-ink">
                                        {{ $motorcycle->displayName() }}
                                    </h3>
                                    <p class="mt-0.5 text-xs text-muted">
                                        {{ $motorcycle->year ? 'Tahun '.$motorcycle->year : 'Tahun belum diisi' }}
                                    </p>
                                </div>
                            </div>

                            <span class="shrink-0 rounded-full border border-line bg-canvas px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-ink/70">
                                {{ $motorcycle->license_plate ?? 'Tanpa plat' }}
                            </span>
                        </div>

                        <dl class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-line bg-canvas/50 px-4 py-3">
                                <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Odometer</dt>
                                <dd class="mt-1 text-sm font-semibold text-ink">
                                    {{ number_format($motorcycle->odometer_current, 0, ',', '.') }} km
                                </dd>
                            </div>
                            <div class="rounded-xl border border-line bg-canvas/50 px-4 py-3">
                                <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Terdaftar</dt>
                                <dd class="mt-1 text-sm font-semibold text-ink">
                                    {{ $motorcycle->created_at?->timezone(config('app.display_timezone'))->translatedFormat('d M Y') }}
                                </dd>
                            </div>
                        </dl>

                        @if ($motorcycle->notes)
                            <p class="mt-4 line-clamp-2 text-xs leading-relaxed text-muted">
                                {{ $motorcycle->notes }}
                            </p>
                        @endif

                        <div class="mt-5 flex items-center gap-2 border-t border-line pt-4">
                            <a
                                href="{{ route('motorcycles.edit', $motorcycle) }}"
                                class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-3.5 py-2 text-xs font-semibold text-ink transition hover:bg-canvas"
                            >
                                <x-icon name="wrench" class="h-3.5 w-3.5" />
                                Edit
                            </a>

                            <button
                                type="button"
                                class="ml-auto inline-flex items-center gap-2 rounded-[10px] px-3.5 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                x-data=""
                                x-on:click.prevent="$dispatch('open-modal', 'delete-motorcycle-{{ $motorcycle->id }}')"
                            >
                                Hapus
                            </button>
                        </div>
                    </article>
                @endforeach
            </section>

            @foreach ($motorcycles as $motorcycle)
                <x-modal name="delete-motorcycle-{{ $motorcycle->id }}" maxWidth="md" focusable>
                    <form method="POST" action="{{ route('motorcycles.destroy', $motorcycle) }}" class="p-6">
                        @csrf
                        @method('delete')

                        <h2 class="text-lg font-semibold tracking-tight text-ink">Hapus motor ini?</h2>
                        <p class="mt-1 text-sm leading-relaxed text-muted">
                            {{ $motorcycle->displayName() }} akan dihapus dari garasi Anda. Riwayat servis yang sudah ada tetap tersimpan.
                        </p>

                        <div class="mt-6 flex justify-end gap-3">
                            <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                            <x-danger-button>Hapus motor</x-danger-button>
                        </div>
                    </form>
                </x-modal>
            @endforeach
        @endif
    </div>
</x-app-layout>
