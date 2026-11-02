@php
    $serviceOrder = $invoice->serviceOrder;
    $customer = $serviceOrder->customer;
    $motorcycle = $serviceOrder->motorcycle;
@endphp

<div class="space-y-5">
    <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Invoice</p>
                <p class="mt-1 font-mono text-xl font-semibold tracking-tight text-ink">{{ $invoice->invoice_number }}</p>
                <p class="mt-1 text-xs text-muted">Dibuat {{ $invoice->formattedCreatedAt() }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $invoice->payment_status->tone() }}">
                    {{ $invoice->payment_status->label() }}
                </span>
                <span class="inline-flex rounded-full border border-line bg-canvas/60 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-muted">
                    {{ $invoice->isDraft() ? 'Draft' : 'Final' }}
                </span>
            </div>
        </div>

        <dl class="mt-5 grid gap-4 border-t border-line pt-5 sm:grid-cols-2">
            <div>
                <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Pelanggan</dt>
                <dd class="mt-1 text-sm font-semibold text-ink">{{ $customer?->name ?? 'Pelanggan' }}</dd>
                <dd class="mt-0.5 text-xs text-muted">{{ $customer?->phone ?? '—' }}</dd>
                <dd class="mt-0.5 truncate text-xs text-muted">{{ $customer?->email }}</dd>
            </div>

            <div>
                <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Motor</dt>
                <dd class="mt-1 text-sm font-semibold text-ink">{{ $motorcycle?->displayName() ?? 'Motor terhapus' }}</dd>
                <dd class="mt-0.5 text-xs text-muted">{{ $motorcycle?->license_plate ?? 'Tanpa plat' }}</dd>
            </div>

            <div>
                <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Work order</dt>
                <dd class="mt-1 font-mono text-sm font-semibold text-ink">{{ $serviceOrder->work_order_number }}</dd>
                <dd class="mt-0.5 text-xs text-muted">{{ $serviceOrder->status->label() }}</dd>
            </div>

            <div>
                <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Status dokumen</dt>
                <dd class="mt-1 text-sm font-semibold text-ink">
                    {{ $invoice->isDraft() ? 'Draft' : 'Final' }}
                </dd>
                <dd class="mt-0.5 text-xs text-muted">
                    {{ $invoice->isDraft()
                        ? 'Finalisasi otomatis saat work order selesai.'
                        : 'Final sejak '.$invoice->formattedIssuedAt().'.' }}
                </dd>
            </div>
        </dl>

        @if (! empty($workOrderUrl))
            <div class="mt-4 border-t border-line pt-4">
                <a href="{{ $workOrderUrl }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-dark transition hover:text-ink">
                    <x-icon name="wrench" class="h-4 w-4" />
                    Buka work order
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>
        @endif
    </section>

    <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="flex items-center justify-between gap-3 border-b border-line px-6 py-4">
            <div>
                <h3 class="text-base font-semibold tracking-tight text-ink">Rincian tagihan</h3>
                <p class="mt-0.5 text-xs text-muted">Harga mengikuti salinan saat layanan dan sparepart dicatat.</p>
            </div>
            <span class="rounded-full border border-line bg-canvas/60 px-3 py-1 text-xs font-semibold text-muted">
                {{ $invoice->items->count() }} item
            </span>
        </div>

        <div class="divide-y divide-line">
            @forelse ($invoice->items as $item)
                <div class="flex items-start justify-between gap-4 px-6 py-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-canvas px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-muted">
                                {{ $item->typeLabel() }}
                            </span>
                            <p class="truncate text-sm font-semibold text-ink">{{ $item->description }}</p>
                        </div>
                        <p class="mt-1 text-xs text-muted">
                            {{ $item->formattedQuantity() }} &times; {{ $item->formattedUnitPrice() }}
                        </p>
                    </div>
                    <p class="shrink-0 text-sm font-semibold text-ink">{{ $item->formattedLineTotal() }}</p>
                </div>
            @empty
                <p class="px-6 py-8 text-center text-sm text-muted">Belum ada item tagihan.</p>
            @endforelse
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
            <h3 class="text-base font-semibold tracking-tight text-ink">Ringkasan</h3>

            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-muted">Subtotal</dt>
                    <dd class="font-semibold text-ink">{{ $invoice->formattedSubtotal() }}</dd>
                </div>

                @if ((float) $invoice->discount_amount > 0)
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Diskon</dt>
                        <dd class="font-semibold text-ink">- {{ $invoice->formattedDiscount() }}</dd>
                    </div>
                @endif

                @if ((float) $invoice->tax_amount > 0)
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Pajak</dt>
                        <dd class="font-semibold text-ink">{{ $invoice->formattedTax() }}</dd>
                    </div>
                @endif

                <div class="flex justify-between gap-4 border-t border-line pt-3">
                    <dt class="font-semibold text-ink">Total</dt>
                    <dd class="font-bold text-ink">{{ $invoice->formattedTotal() }}</dd>
                </div>

                <div class="flex justify-between gap-4">
                    <dt class="text-muted">Dibayar</dt>
                    <dd class="font-semibold text-ink">{{ $invoice->formattedPaid() }}</dd>
                </div>

                <div class="flex justify-between gap-4">
                    <dt class="font-semibold text-ink">Sisa tagihan</dt>
                    <dd class="font-bold {{ $invoice->isPaid() ? 'text-brand-dark' : 'text-red-600' }}">
                        {{ $invoice->formattedRemaining() }}
                    </dd>
                </div>
            </dl>

            @if ($invoice->deposit_required)
                <div class="mt-4 rounded-xl border border-brand-accent/40 bg-brand-soft/40 px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-brand-dark">Deposit</p>
                    <p class="mt-1 text-sm font-semibold text-ink">
                        {{ $invoice->formattedDepositPercentage() }} &middot; {{ $invoice->formattedDepositAmount() }}
                    </p>
                    <p class="mt-0.5 text-xs text-muted">Deposit dicatat sebagai pembayaran terpisah.</p>
                </div>
            @endif
        </section>

        <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-base font-semibold tracking-tight text-ink">Pembayaran</h3>
                <span class="rounded-full border border-line bg-canvas/60 px-2.5 py-1 text-[11px] font-semibold text-muted">
                    {{ $invoice->payments->count() }} transaksi
                </span>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($invoice->payments as $payment)
                    <div class="rounded-xl border border-line bg-canvas/30 px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-mono text-[11px] font-semibold text-ink">{{ $payment->payment_reference }}</p>
                                <p class="mt-0.5 text-xs text-muted">{{ $payment->methodLabel() }} &middot; {{ $payment->formattedPaidAt() }}</p>
                            </div>
                            <p class="shrink-0 text-sm font-semibold text-ink">{{ $payment->formattedAmount() }}</p>
                        </div>
                        <p class="mt-1 text-[11px] text-muted">Diterima {{ $payment->receivedBy?->name ?? 'Sistem' }}</p>
                        @if ($payment->notes)
                            <p class="mt-1 text-[11px] italic text-muted">{{ $payment->notes }}</p>
                        @endif
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-line bg-canvas/30 px-4 py-5 text-center text-sm text-muted">
                        Belum ada pembayaran.
                    </p>
                @endforelse
            </div>
        </section>
    </div>
</div>
