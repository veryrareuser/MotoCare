<x-app-layout title="Edit Layanan">
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
            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Edit layanan</h2>
            <p class="mt-1 text-sm leading-relaxed text-muted">
                Perbarui {{ $serviceType->name }}. Perubahan harga tidak mengubah salinan harga pada booking lama.
            </p>

            <form method="POST" action="{{ route('staff.services.update', $serviceType) }}" class="mt-6 space-y-6">
                @csrf
                @method('patch')

                @include('staff.services.partials.form', ['serviceType' => $serviceType])

                <div class="flex flex-wrap items-center gap-3 border-t border-line pt-5">
                    <x-primary-button>Simpan perubahan</x-primary-button>

                    <a
                        href="{{ route('staff.services.index') }}"
                        class="inline-flex items-center justify-center rounded-[10px] px-5 py-3 text-sm font-semibold text-muted transition hover:bg-canvas hover:text-ink"
                    >
                        Batal
                    </a>
                </div>
            </form>

            @can('delete', $serviceType)
                <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-line pt-5">
                    <p class="max-w-md text-xs leading-relaxed text-muted">
                        Menghapus layanan akan menyembunyikannya dari katalog. Riwayat servis tetap tersimpan.
                    </p>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-[10px] px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                        x-data=""
                        x-on:click.prevent="$dispatch('open-modal', 'delete-service-{{ $serviceType->id }}')"
                    >
                        Hapus layanan
                    </button>
                </div>

                <x-modal name="delete-service-{{ $serviceType->id }}" maxWidth="md" focusable>
                    <form method="POST" action="{{ route('staff.services.destroy', $serviceType) }}" class="p-6">
                        @csrf
                        @method('delete')

                        <h2 class="text-lg font-semibold tracking-tight text-ink">Hapus layanan ini?</h2>
                        <p class="mt-1 text-sm leading-relaxed text-muted">
                            {{ $serviceType->name }} akan disembunyikan dari katalog. Riwayat servis yang sudah ada tetap tersimpan.
                        </p>

                        <div class="mt-6 flex justify-end gap-3">
                            <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                            <x-danger-button>Hapus layanan</x-danger-button>
                        </div>
                    </form>
                </x-modal>
            @endcan
        </div>
    </div>
</x-app-layout>
