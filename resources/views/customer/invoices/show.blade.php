<x-app-layout :title="'Invoice '.$invoice->invoice_number">
    <div class="mx-auto max-w-5xl space-y-6">
        <a
            href="{{ route('invoices.index') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-ink"
        >
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke invoice
        </a>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Keuangan</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Detail Invoice</h2>
                <p class="mt-1 text-sm leading-relaxed text-muted">
                    Rincian layanan, sparepart, dan pembayaran servis Anda.
                </p>
            </div>

            <a
                href="{{ route('invoices.receipt', $invoice) }}"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-canvas"
            >
                <x-icon name="clipboard" class="h-4 w-4" />
                Cetak kuitansi
            </a>
        </div>

        @include('invoices.partials.summary', [
            'invoice' => $invoice,
            'workOrderUrl' => null,
        ])

        <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-brand-dark">
                    <x-icon name="shield" class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="text-base font-semibold tracking-tight text-ink">Butuh bantuan?</h3>
                    <p class="mt-1 text-sm leading-relaxed text-muted">
                        Hubungi kasir workshop bila ada pembayaran yang belum tercatat atau rincian tagihan yang perlu dikonfirmasi.
                    </p>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
