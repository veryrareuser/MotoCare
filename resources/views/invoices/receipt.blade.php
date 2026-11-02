@php
    $serviceOrder = $invoice->serviceOrder;
    $customer = $serviceOrder->customer;
    $motorcycle = $serviceOrder->motorcycle;
    $paidPayments = $invoice->payments->where('payment_status', App\Enums\PaymentStatus::Paid);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Kuitansi {{ $invoice->invoice_number }} — {{ config('app.name', 'MotoCare') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            @media print {
                .no-print {
                    display: none !important;
                }

                body {
                    background: #ffffff !important;
                }

                .receipt-sheet {
                    border: 0 !important;
                    box-shadow: none !important;
                    margin: 0 !important;
                    max-width: 100% !important;
                }
            }
        </style>
    </head>
    <body class="min-h-screen bg-canvas font-sans text-ink antialiased">
        <div class="no-print mx-auto flex max-w-3xl items-center justify-between gap-3 px-5 py-5 sm:px-8">
            <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-ink">
                <x-icon name="arrow-left" class="h-4 w-4" />
                {{ $backLabel }}
            </a>

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-[10px] bg-brand px-4 py-2.5 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent"
            >
                <x-icon name="clipboard" class="h-4 w-4" />
                Cetak kuitansi
            </button>
        </div>

        <main class="mx-auto max-w-3xl px-5 pb-12 sm:px-8">
            <article class="receipt-sheet rounded-2xl border border-line bg-white p-7 shadow-card sm:p-10">
                <header class="flex flex-col gap-5 border-b border-line pb-6 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-center gap-3">
                        <x-application-logo class="h-11 w-11" />
                        <div>
                            <p class="text-lg font-black tracking-tight text-ink">MOTOCARE</p>
                            <p class="text-xs text-muted">Workshop &amp; Perawatan Motor</p>
                        </div>
                    </div>

                    <div class="sm:text-right">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-muted">Kuitansi</p>
                        <p class="mt-1 font-mono text-lg font-semibold tracking-tight text-ink">{{ $invoice->invoice_number }}</p>
                        <p class="mt-1 text-xs text-muted">{{ $invoice->formattedIssuedAt() }}</p>
                        <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $invoice->payment_status->tone() }}">
                            {{ $invoice->payment_status->label() }}
                        </span>
                    </div>
                </header>

                <section class="grid gap-5 border-b border-line py-6 sm:grid-cols-2">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Pelanggan</p>
                        <p class="mt-1.5 text-sm font-semibold text-ink">{{ $customer?->name ?? 'Pelanggan' }}</p>
                        <p class="mt-0.5 text-xs text-muted">{{ $customer?->phone ?? '—' }}</p>
                        <p class="mt-0.5 break-all text-xs text-muted">{{ $customer?->email }}</p>
                    </div>

                    <div class="sm:text-right">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Motor</p>
                        <p class="mt-1.5 text-sm font-semibold text-ink">{{ $motorcycle?->displayName() ?? 'Motor terhapus' }}</p>
                        <p class="mt-0.5 text-xs text-muted">{{ $motorcycle?->license_plate ?? 'Tanpa plat' }}</p>
                        <p class="mt-0.5 font-mono text-xs text-muted">{{ $serviceOrder->work_order_number }}</p>
                    </div>
                </section>

                <section class="py-6">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-line text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
                                <th class="pb-3 font-bold">Deskripsi</th>
                                <th class="pb-3 text-center font-bold">Qty</th>
                                <th class="pb-3 text-right font-bold">Harga</th>
                                <th class="pb-3 text-right font-bold">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @forelse ($invoice->items as $item)
                                <tr>
                                    <td class="py-3 pr-3">
                                        <p class="font-semibold text-ink">{{ $item->description }}</p>
                                        <p class="mt-0.5 text-[11px] uppercase tracking-wide text-muted">{{ $item->typeLabel() }}</p>
                                    </td>
                                    <td class="py-3 text-center text-ink/80">{{ $item->formattedQuantity() }}</td>
                                    <td class="py-3 text-right text-ink/80">{{ $item->formattedUnitPrice() }}</td>
                                    <td class="py-3 text-right font-semibold text-ink">{{ $item->formattedLineTotal() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-muted">Belum ada item tagihan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </section>

                <section class="ml-auto max-w-xs space-y-2 border-t border-line pt-5 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-muted">Subtotal</span>
                        <span class="font-semibold text-ink">{{ $invoice->formattedSubtotal() }}</span>
                    </div>

                    @if ((float) $invoice->discount_amount > 0)
                        <div class="flex justify-between gap-4">
                            <span class="text-muted">Diskon</span>
                            <span class="font-semibold text-ink">- {{ $invoice->formattedDiscount() }}</span>
                        </div>
                    @endif

                    @if ((float) $invoice->tax_amount > 0)
                        <div class="flex justify-between gap-4">
                            <span class="text-muted">Pajak</span>
                            <span class="font-semibold text-ink">{{ $invoice->formattedTax() }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between gap-4 border-t border-line pt-2">
                        <span class="font-semibold text-ink">Total</span>
                        <span class="text-base font-bold text-ink">{{ $invoice->formattedTotal() }}</span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-muted">Dibayar</span>
                        <span class="font-semibold text-ink">{{ $invoice->formattedPaid() }}</span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="font-semibold text-ink">Sisa</span>
                        <span class="font-bold {{ $invoice->isPaid() ? 'text-brand-dark' : 'text-red-600' }}">{{ $invoice->formattedRemaining() }}</span>
                    </div>

                    @if ($invoice->deposit_required)
                        <div class="flex justify-between gap-4">
                            <span class="text-muted">Deposit ({{ $invoice->formattedDepositPercentage() }})</span>
                            <span class="font-semibold text-ink">{{ $invoice->formattedDepositAmount() }}</span>
                        </div>
                    @endif
                </section>

                <section class="mt-8 border-t border-line pt-6">
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Riwayat pembayaran</h2>

                    @if ($paidPayments->isEmpty())
                        <p class="mt-3 text-sm text-muted">Belum ada pembayaran diterima.</p>
                    @else
                        <div class="mt-3 space-y-2">
                            @foreach ($paidPayments as $payment)
                                <div class="flex items-start justify-between gap-4 rounded-xl border border-line bg-canvas/40 px-4 py-3">
                                    <div class="min-w-0">
                                        <p class="font-mono text-[11px] font-semibold text-ink">{{ $payment->payment_reference }}</p>
                                        <p class="mt-0.5 text-xs text-muted">
                                            {{ $payment->methodLabel() }} &middot; {{ $payment->formattedPaidAt() }}
                                        </p>
                                        @if ($payment->notes)
                                            <p class="mt-0.5 text-[11px] italic text-muted">{{ $payment->notes }}</p>
                                        @endif
                                    </div>
                                    <p class="shrink-0 text-sm font-semibold text-ink">{{ $payment->formattedAmount() }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <footer class="mt-8 border-t border-line pt-5 text-center">
                    <p class="text-xs leading-relaxed text-muted">
                        Terima kasih telah mempercayakan perawatan motor Anda kepada MotoCare.
                        Kuitansi ini sah tanpa tanda tangan.
                    </p>
                    <p class="mt-1 text-[10px] text-muted">
                        Dicetak {{ now(config('app.display_timezone'))->translatedFormat('d M Y H:i') }} WIB
                    </p>
                </footer>
            </article>
        </main>
    </body>
</html>
