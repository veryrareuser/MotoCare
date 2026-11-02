@php
    use App\Enums\PaymentMethod;

    $canManage = auth()->user()->can('recordPayment', $invoice);
@endphp

<x-app-layout :title="'Invoice '.$invoice->invoice_number">
    <div class="mx-auto max-w-6xl space-y-6">
        <a
            href="{{ route('staff.invoices.index', ['date' => $invoice->created_at->timezone(config('app.display_timezone'))->toDateString()]) }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-ink"
        >
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke invoice
        </a>

        @if ($errors->has('invoice'))
            <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                <p>{{ $errors->first('invoice') }}</p>
            </div>
        @endif

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Keuangan</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Invoice</h2>
                <p class="mt-1 text-sm leading-relaxed text-muted">
                    Tagihan servis, deposit, dan pembayaran pelanggan.
                </p>
            </div>

            <a
                href="{{ route('staff.invoices.receipt', $invoice) }}"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-canvas"
            >
                <x-icon name="clipboard" class="h-4 w-4" />
                Cetak kuitansi
            </a>
        </div>

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
            <div>
                @include('invoices.partials.summary', [
                    'invoice' => $invoice,
                    'workOrderUrl' => route('staff.service-orders.show', $invoice->serviceOrder),
                ])
            </div>

            <aside class="space-y-5">
                @if ($canManage)
                    @if ($invoice->isDraft())
                        <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                            <h3 class="text-base font-semibold tracking-tight text-ink">Deposit</h3>
                            <p class="mt-0.5 text-xs text-muted">Opsional. Persentase default 50%.</p>

                            <form method="POST" action="{{ route('staff.invoices.deposit.update', $invoice) }}" class="mt-4 space-y-4">
                                @csrf
                                @method('patch')

                                <input type="hidden" name="deposit_required" value="0">

                                <label class="flex items-center gap-2.5">
                                    <input
                                        type="checkbox"
                                        name="deposit_required"
                                        value="1"
                                        class="rounded border-line text-brand focus:ring-brand-accent"
                                        @checked(old('deposit_required', $invoice->deposit_required))
                                    >
                                    <span class="text-sm font-semibold text-ink">Terapkan deposit</span>
                                </label>

                                <div>
                                    <x-input-label for="deposit_percentage" value="Persentase deposit" />
                                    <x-text-input
                                        id="deposit_percentage"
                                        name="deposit_percentage"
                                        type="number"
                                        min="1"
                                        max="100"
                                        step="0.01"
                                        class="mt-2 block w-full"
                                        :value="old('deposit_percentage', $invoice->deposit_percentage ?? config('invoice.deposit_percentage'))"
                                    />
                                    <x-input-error :messages="$errors->get('deposit_percentage')" class="mt-2" />
                                    <x-input-error :messages="$errors->get('deposit_required')" class="mt-2" />
                                </div>

                                <p class="text-xs text-muted">
                                    Target deposit saat ini: <span class="font-semibold text-ink">{{ $invoice->formattedDepositAmount() }}</span>
                                </p>

                                <x-secondary-button type="submit" class="w-full">Simpan deposit</x-secondary-button>
                            </form>
                        </section>
                    @endif

                    @if ((float) $invoice->remaining_amount > 0)
                        <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                            <h3 class="text-base font-semibold tracking-tight text-ink">Catat pembayaran</h3>
                            <p class="mt-0.5 text-xs text-muted">
                                Sisa tagihan <span class="font-semibold text-ink">{{ $invoice->formattedRemaining() }}</span>.
                            </p>

                            <form method="POST" action="{{ route('staff.invoices.payments.store', $invoice) }}" class="mt-4 space-y-4">
                                @csrf

                                <div>
                                    <x-input-label for="amount" value="Jumlah (Rp)" />
                                    <x-text-input
                                        id="amount"
                                        name="amount"
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        max="{{ (float) $invoice->remaining_amount }}"
                                        class="mt-2 block w-full"
                                        :value="old('amount', (float) $invoice->remaining_amount)"
                                    />
                                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="payment_method" value="Metode pembayaran" />
                                    <x-select-input id="payment_method" name="payment_method" class="mt-2 block w-full">
                                        @foreach (PaymentMethod::cases() as $method)
                                            <option value="{{ $method->value }}" @selected(old('payment_method', 'cash') === $method->value)>
                                                {{ $method->label() }}
                                            </option>
                                        @endforeach
                                    </x-select-input>
                                    <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="notes" value="Catatan (opsional)" />
                                    <textarea
                                        id="notes"
                                        name="notes"
                                        rows="2"
                                        class="mt-2 block w-full rounded-[10px] border-line bg-white px-4 py-3 text-sm text-ink shadow-sm placeholder:text-muted/60 focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/25"
                                        placeholder="Contoh: DP servis atau pelunasan."
                                    >{{ old('notes') }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>

                                <x-primary-button type="submit" class="w-full">Catat pembayaran</x-primary-button>
                            </form>
                        </section>
                    @else
                        <section class="rounded-2xl border border-brand-accent/40 bg-brand-soft/40 p-6 shadow-card">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-brand-dark shadow-sm">
                                    <x-icon name="check" class="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold tracking-tight text-ink">Invoice lunas</h3>
                                    <p class="mt-1 text-sm leading-relaxed text-muted">
                                        Seluruh tagihan sudah dibayar. Kuitansi dapat dicetak untuk pelanggan.
                                    </p>
                                </div>
                            </div>
                        </section>
                    @endif
                @else
                    <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                        <h3 class="text-base font-semibold tracking-tight text-ink">Akses baca</h3>
                        <p class="mt-1 text-sm leading-relaxed text-muted">
                            Hanya kasir yang dapat mengubah deposit dan mencatat pembayaran.
                        </p>
                    </section>
                @endif

                <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                    <h3 class="text-base font-semibold tracking-tight text-ink">Kuitansi</h3>
                    <p class="mt-1 text-sm leading-relaxed text-muted">
                        Cetak bukti pembayaran untuk pelanggan. Halaman kuitansi siap dicetak.
                    </p>
                    <a
                        href="{{ route('staff.invoices.receipt', $invoice) }}"
                        target="_blank"
                        rel="noopener"
                        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-[10px] bg-ink px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink/90"
                    >
                        <x-icon name="cash" class="h-4 w-4" />
                        Buka kuitansi
                    </a>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
