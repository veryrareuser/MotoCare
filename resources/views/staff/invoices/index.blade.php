@php
    use App\Enums\InvoiceStatus;

    $total = (int) $counts->sum();
    $unpaid = (int) ($counts[InvoiceStatus::Unpaid->value] ?? 0);
    $partial = (int) ($counts[InvoiceStatus::PartiallyPaid->value] ?? 0);
    $paid = (int) ($counts[InvoiceStatus::Paid->value] ?? 0);
@endphp

<x-app-layout title="Invoice & Pembayaran">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Keuangan</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Invoice & Pembayaran</h2>
                <p class="mt-1 max-w-2xl text-sm leading-relaxed text-muted">
                    Pantau tagihan servis, catat pembayaran bertahap, dan cetak kuitansi pelanggan.
                </p>
            </div>

            <form method="GET" action="{{ route('staff.invoices.index') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label for="date" value="Tanggal invoice" />
                    <x-text-input
                        id="date"
                        name="date"
                        type="date"
                        class="mt-2 block"
                        :value="$date->toDateString()"
                    />
                </div>

                @if ($status !== null)
                    <input type="hidden" name="status" value="{{ $status->value }}">
                @endif

                @if ($search !== '')
                    <input type="hidden" name="search" value="{{ $search }}">
                @endif

                <x-secondary-button type="submit">Tampilkan</x-secondary-button>

                @if ($date->toDateString() !== now(config('app.display_timezone'))->toDateString())
                    <a href="{{ route('staff.invoices.index') }}" class="text-sm font-semibold text-muted transition hover:text-ink">
                        Hari ini
                    </a>
                @endif
            </form>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Total invoice</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $total }}</p>
                <p class="mt-1 text-xs text-muted">{{ $date->translatedFormat('l, d M Y') }}</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Belum dibayar</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $unpaid }}</p>
                <p class="mt-1 text-xs text-muted">Belum ada pembayaran</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Dibayar sebagian</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $partial }}</p>
                <p class="mt-1 text-xs text-muted">Termasuk deposit</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Lunas</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $paid }}</p>
                <p class="mt-1 text-xs text-muted">Tagihan selesai</p>
            </div>
        </section>

        <form method="GET" action="{{ route('staff.invoices.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">

            <div class="relative min-w-[240px] flex-1">
                <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                <x-text-input
                    name="search"
                    type="search"
                    class="block w-full pl-10"
                    :value="$search"
                    placeholder="Cari invoice, work order, pelanggan, atau plat"
                />
            </div>

            <x-select-input name="status" class="min-w-[200px]">
                <option value="">Semua status</option>
                @foreach (InvoiceStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($status === $case)>{{ $case->label() }}</option>
                @endforeach
            </x-select-input>

            <x-secondary-button type="submit">Terapkan</x-secondary-button>

            @if ($search !== '' || $status !== null)
                <a href="{{ route('staff.invoices.index', ['date' => $date->toDateString()]) }}" class="text-sm font-semibold text-muted transition hover:text-ink">
                    Reset
                </a>
            @endif
        </form>

        <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="border-b border-line bg-canvas/60">
                        <tr class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
                            <th class="px-6 py-4 font-bold">Invoice</th>
                            <th class="px-6 py-4 font-bold">Pelanggan & motor</th>
                            <th class="px-6 py-4 font-bold">Work order</th>
                            <th class="px-6 py-4 text-right font-bold">Total</th>
                            <th class="px-6 py-4 text-right font-bold">Dibayar</th>
                            <th class="px-6 py-4 text-right font-bold">Sisa</th>
                            <th class="px-6 py-4 font-bold">Status</th>
                            <th class="px-6 py-4 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($invoices as $invoice)
                            <tr class="transition hover:bg-canvas/40">
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-lg bg-brand-soft px-2.5 py-1.5 font-mono text-[11px] font-bold text-brand-dark">
                                        {{ $invoice->invoice_number }}
                                    </span>
                                    <p class="mt-1 text-[10px] uppercase tracking-wide text-muted">
                                        {{ $invoice->created_at->timezone(config('app.display_timezone'))->format('H:i') }} WIB
                                        &middot; {{ $invoice->isDraft() ? 'Draft' : 'Final' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-ink">{{ $invoice->serviceOrder?->customer?->name ?? 'Pelanggan' }}</p>
                                    <p class="mt-0.5 text-xs text-muted">
                                        {{ $invoice->serviceOrder?->motorcycle?->displayName() ?? 'Motor terhapus' }}
                                        &middot; {{ $invoice->serviceOrder?->motorcycle?->license_plate ?? 'Tanpa plat' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="font-mono text-[11px] font-semibold text-ink">{{ $invoice->serviceOrder?->work_order_number }}</span>
                                    <p class="mt-0.5 text-xs text-muted">{{ $invoice->serviceOrder?->status->label() }}</p>
                                </td>

                                <td class="px-6 py-4 text-right text-sm font-semibold text-ink">{{ $invoice->formattedTotal() }}</td>
                                <td class="px-6 py-4 text-right text-sm text-ink/80">{{ $invoice->formattedPaid() }}</td>
                                <td class="px-6 py-4 text-right text-sm font-semibold {{ $invoice->isPaid() ? 'text-brand-dark' : 'text-red-600' }}">
                                    {{ $invoice->formattedRemaining() }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $invoice->payment_status->tone() }}">
                                        {{ $invoice->payment_status->label() }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a
                                        href="{{ route('staff.invoices.show', $invoice) }}"
                                        class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-3 py-1.5 text-xs font-semibold text-ink transition hover:bg-canvas"
                                    >
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-soft text-brand-dark">
                                            <x-icon name="cash" class="h-6 w-6" />
                                        </div>
                                        <p class="mt-4 text-sm font-semibold text-ink">Tidak ada invoice</p>
                                        <p class="mt-1 text-sm text-muted">
                                            {{ $search !== '' || $status !== null
                                                ? 'Ubah kata kunci atau filter status.'
                                                : 'Belum ada invoice pada tanggal ini.' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
