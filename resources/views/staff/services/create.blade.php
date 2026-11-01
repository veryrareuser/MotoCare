<x-app-layout title="Tambah Layanan">
    <div class="mx-auto max-w-3xl space-y-6">
        <a
            href="{{ route('staff.services.index') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-ink"
        >
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar layanan
        </a>

        <div class="rounded-2xl border border-line bg-white p-6 shadow-card sm:p-8">
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Katalog</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Tambah layanan</h2>
            <p class="mt-1 text-sm leading-relaxed text-muted">
                Harga di sini adalah harga dasar. Harga yang sudah dipakai booking tetap tersimpan sebagai salinan.
            </p>

            <form method="POST" action="{{ route('staff.services.store') }}" class="mt-6 space-y-6">
                @csrf

                @include('staff.services.partials.form', ['serviceType' => null])

                <div class="flex flex-wrap items-center gap-3 border-t border-line pt-5">
                    <x-primary-button>Simpan layanan</x-primary-button>

                    <a
                        href="{{ route('staff.services.index') }}"
                        class="inline-flex items-center justify-center rounded-[10px] px-5 py-3 text-sm font-semibold text-muted transition hover:bg-canvas hover:text-ink"
                    >
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
