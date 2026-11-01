<x-app-layout title="Tambah Motor">
    <div class="mx-auto max-w-3xl space-y-6">
        <a
            href="{{ route('motorcycles.index') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-ink"
        >
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke kendaraan saya
        </a>

        <div class="rounded-2xl border border-line bg-white p-6 shadow-card sm:p-8">
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Garasi</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Tambah motor</h2>
            <p class="mt-1 text-sm leading-relaxed text-muted">
                Anda dapat mendaftarkan maksimal {{ $limit }} motor per akun.
            </p>

            @if ($errors->has('motorcycle'))
                <div class="mt-5 flex items-start gap-3 rounded-xl border border-warm bg-warm/60 px-4 py-3 text-sm text-[#8A5A00]">
                    <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                    <p>{{ $errors->first('motorcycle') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('motorcycles.store') }}" class="mt-6 space-y-6">
                @csrf

                @include('customer.motorcycles.partials.form', ['motorcycle' => null])

                <div class="flex flex-wrap items-center gap-3 border-t border-line pt-5">
                    <x-primary-button>Simpan motor</x-primary-button>

                    <a
                        href="{{ route('motorcycles.index') }}"
                        class="inline-flex items-center justify-center rounded-[10px] px-5 py-3 text-sm font-semibold text-muted transition hover:bg-canvas hover:text-ink"
                    >
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
