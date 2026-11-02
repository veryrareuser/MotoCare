<x-app-layout title="Invoice Saya">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Keuangan</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Invoice Saya</h2>
                <p class="mt-1 max-w-xl text-sm leading-relaxed text-muted">
                    Tagihan servis Anda, termasuk deposit dan pembayaran bertahap.
                </p>
            </div>

            <a
                href="{{ route('bookings.index') }}"
                class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-canvas"
            >
                <x-icon name="calendar" class="h-4 w-4" />
                Lihat booking
            </a>
        </div>

        @if ($invoices->isEmpty())
            <section class="rounded-2xl border border-line bg-white p-8 shadow-card sm:p-12">
                <div class="mx-auto flex max-w-md flex-col items-center text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-soft text-brand-dark">
                        <x-icon name="cash" class="h-8 w-8" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-ink">Belum ada invoice</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">
                        Invoice dibuat otomatis saat motor Anda check-in di workshop.
                    </p>
                    <a
                        href="{{ route('bookings.create') }}"
                        class="mt-6 inline-flex items-center gap-2 rounded-[10px] bg-brand px-5 py-3 text-sm font-bold text-ink transition hover:bg-brand-accent"
                    >
                        <x-icon name="plus" class="h-4 w-4" />
                        Buat Booking
                    </a>
                </div>
            </section>
        @else
            <div class="space-y-4">
                @foreach ($invoices as $invoice)
                    @php
                        $serviceOrder = $invoice->serviceOrder;
                        $motorcycle = $serviceOrder?->motorcycle;
                    @endphp

                    <article class="rounded-2xl border border-line bg-white p-6 shadow-card">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-lg bg-brand-soft px-2.5 py-1.5 font-mono text-[11px] font-bold text-brand-dark">
                                        {{ $invoice->invoice_number }}
                                    </span>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $invoice->payment_status->tone() }}">
                                        {{ $invoice->payment_status->label() }}
                                    </span>
                                    <span class="inline-flex rounded-full border border-line bg-canvas/60 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-muted">
                                        {{ $invoice->isDraft() ? 'Draft' : 'Final' }}
                                    </span>
                                </div>

                                <p class="mt-3 text-sm font-semibold text-ink">
                                    {{ $motorcycle?->displayName() ?? 'Motor terhapus' }}
                                    &middot; {{ $motorcycle?->license_plate ?? 'Tanpa plat' }}
                                </p>
                                <p class="mt-0.5 text-xs text-muted">
                                    {{ $serviceOrder?->work_order_number }} &middot; {{ $invoice->formattedCreatedAt() }}
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-5">
                                <div class="text-right">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Total</p>
                                    <p class="mt-1 text-sm font-semibold text-ink">{{ $invoice->formattedTotal() }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Sisa</p>
                                    <p class="mt-1 text-sm font-bold {{ $invoice->isPaid() ? 'text-brand-dark' : 'text-red-600' }}">
                                        {{ $invoice->formattedRemaining() }}
                                    </p>
                                </div>
                                <a
                                    href="{{ route('invoices.show', $invoice) }}"
                                    class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-3 py-2 text-xs font-semibold text-ink transition hover:bg-canvas"
                                >
                                    Detail
                                    <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
