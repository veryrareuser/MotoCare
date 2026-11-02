@php
    use App\Enums\ServiceOrderStatus;

    $statuses = ServiceOrderStatus::cases();
    $currentIndex = array_search($serviceOrder->status, $statuses, true);
    $motorcycle = $serviceOrder->motorcycle;
    $invoice = $serviceOrder->invoice;
    $isClosed = $serviceOrder->status->isLocked();
@endphp

<x-app-layout :title="'Servis '.$serviceOrder->work_order_number">
    <div class="mx-auto max-w-6xl space-y-6">
        <a
            href="{{ $isClosed ? route('service-history.index') : route('tracking.index') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-ink"
        >
            <x-icon name="arrow-left" class="h-4 w-4" />
            {{ $isClosed ? 'Kembali ke riwayat servis' : 'Kembali ke tracking servis' }}
        </a>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-lg bg-brand-soft px-2.5 py-1.5 font-mono text-[11px] font-bold text-brand-dark">
                        {{ $serviceOrder->work_order_number }}
                    </span>
                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $serviceOrder->status->tone() }}">
                        {{ $serviceOrder->status->label() }}
                    </span>
                    <span class="inline-flex rounded-full border border-line bg-canvas/60 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-muted">
                        {{ $serviceOrder->queueLabel() }}
                    </span>
                </div>

                <h2 class="mt-3 text-2xl font-semibold tracking-tight text-ink">
                    {{ $motorcycle?->displayName() ?? 'Motor terhapus' }}
                </h2>
                <p class="mt-1 text-sm text-muted">
                    {{ $motorcycle?->license_plate ?? 'Tanpa plat' }}
                    &middot; Check-in {{ $serviceOrder->checkedInAtLocal()->translatedFormat('d M Y H:i') }}
                </p>
            </div>

            @if ($invoice)
                <a
                    href="{{ route('invoices.show', $invoice) }}"
                    class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-canvas"
                >
                    <x-icon name="cash" class="h-4 w-4" />
                    Lihat invoice
                </a>
            @endif
        </div>

        <section class="rounded-2xl border border-line bg-white p-6 shadow-card sm:p-7">
            <h3 class="text-base font-semibold tracking-tight text-ink">Progres servis</h3>
            <ol class="mt-5 grid gap-3 sm:grid-cols-5">
                @foreach ($statuses as $index => $status)
                    @php
                        $isDone = $index <= $currentIndex;
                        $isCurrent = $index === $currentIndex;
                    @endphp

                    <li class="rounded-xl border px-3 py-2.5 {{ $isCurrent ? 'border-brand-dark bg-brand-soft' : ($isDone ? 'border-line bg-canvas/60' : 'border-dashed border-line bg-white') }}">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-bold {{ $isDone ? 'bg-brand text-ink' : 'bg-canvas text-muted' }}">
                                @if ($isDone)
                                    <x-icon name="check" class="h-3 w-3" />
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </span>
                            <span class="text-[11px] font-semibold leading-tight {{ $isDone ? 'text-ink' : 'text-muted' }}">
                                {{ $status->label() }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Odometer masuk</p>
                <p class="mt-2 text-sm font-semibold text-ink">{{ $serviceOrder->formattedOdometerIn() }}</p>
                <p class="mt-1 text-xs text-muted">Saat check-in</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Odometer keluar</p>
                <p class="mt-2 text-sm font-semibold text-ink">{{ $serviceOrder->formattedOdometerOut() }}</p>
                <p class="mt-1 text-xs text-muted">Saat servis selesai</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Mekanik</p>
                <p class="mt-2 text-sm font-semibold text-ink">{{ $serviceOrder->mechanicNames() ?: 'Belum ditugaskan' }}</p>
                <p class="mt-1 text-xs text-muted">Tim yang menangani</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Total servis</p>
                <p class="mt-2 text-sm font-semibold text-ink">{{ $serviceOrder->formattedTotalAmount() }}</p>
                <p class="mt-1 text-xs text-muted">Layanan + sparepart</p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
            <div class="space-y-6">
                <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
                    <div class="border-b border-line px-6 py-4">
                        <h3 class="text-base font-semibold tracking-tight text-ink">Layanan</h3>
                        <p class="mt-0.5 text-xs text-muted">Harga dan durasi yang disepakati saat servis.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="border-b border-line bg-canvas/60">
                                <tr class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
                                    <th class="px-6 py-3 font-bold">Layanan</th>
                                    <th class="px-6 py-3 font-bold">Qty</th>
                                    <th class="px-6 py-3 font-bold">Harga</th>
                                    <th class="px-6 py-3 text-right font-bold">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @forelse ($serviceOrder->services as $service)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-semibold text-ink">{{ $service->description }}</p>
                                            <p class="mt-0.5 text-xs text-muted">
                                                {{ $service->serviceType?->name ?? 'Layanan' }}
                                                &middot; {{ $service->formattedDuration() }}
                                            </p>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-ink">{{ $service->formattedQuantity() }}</td>
                                        <td class="px-6 py-4 text-sm text-ink">{{ $service->formattedPrice() }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-semibold text-ink">{{ $service->formattedLineTotal() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-xs text-muted">
                                            Belum ada layanan yang dicatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between border-t border-line px-6 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-muted">Subtotal layanan</p>
                        <p class="text-sm font-bold text-ink">{{ $serviceOrder->formattedServiceAmount() }}</p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
                    <div class="border-b border-line px-6 py-4">
                        <h3 class="text-base font-semibold tracking-tight text-ink">Sparepart</h3>
                        <p class="mt-0.5 text-xs text-muted">Sparepart yang dipakai beserta harga saat dipasang.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="border-b border-line bg-canvas/60">
                                <tr class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
                                    <th class="px-6 py-3 font-bold">Sparepart</th>
                                    <th class="px-6 py-3 font-bold">Qty</th>
                                    <th class="px-6 py-3 font-bold">Harga</th>
                                    <th class="px-6 py-3 text-right font-bold">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @forelse ($serviceOrder->parts as $part)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-semibold text-ink">{{ $part->sparepart?->name ?? 'Sparepart terhapus' }}</p>
                                            <p class="mt-0.5 font-mono text-xs text-muted">{{ $part->sparepart?->sku ?? '—' }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-ink">
                                            {{ $part->formattedQuantity() }} {{ $part->sparepart?->unit }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-ink">{{ $part->formattedPrice() }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-semibold text-ink">{{ $part->formattedLineTotal() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-xs text-muted">
                                            Tidak ada sparepart yang dipakai.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between border-t border-line px-6 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-muted">Subtotal sparepart</p>
                        <p class="text-sm font-bold text-ink">{{ $serviceOrder->formattedPartAmount() }}</p>
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                    <h3 class="text-base font-semibold tracking-tight text-ink">Ringkasan biaya</h3>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-muted">Layanan</dt>
                            <dd class="font-semibold text-ink">{{ $serviceOrder->formattedServiceAmount() }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-muted">Sparepart</dt>
                            <dd class="font-semibold text-ink">{{ $serviceOrder->formattedPartAmount() }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-line pt-3">
                            <dt class="font-semibold text-ink">Total</dt>
                            <dd class="text-base font-bold text-ink">{{ $serviceOrder->formattedTotalAmount() }}</dd>
                        </div>
                    </dl>

                    @if ($invoice)
                        <div class="mt-5 rounded-xl border border-line bg-canvas/40 px-4 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-semibold text-ink">{{ $invoice->invoice_number }}</span>
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $invoice->payment_status->tone() }}">
                                    {{ $invoice->payment_status->label() }}
                                </span>
                            </div>
                            <p class="mt-2 text-xs text-muted">
                                Dibayar {{ $invoice->formattedPaid() }} &middot; sisa {{ $invoice->formattedRemaining() }}
                            </p>
                            <a
                                href="{{ route('invoices.show', $invoice) }}"
                                class="mt-3 inline-flex items-center gap-2 text-xs font-semibold text-brand-dark transition hover:text-ink"
                            >
                                Buka invoice
                                <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                            </a>
                        </div>
                    @endif
                </section>

                <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                    <h3 class="text-base font-semibold tracking-tight text-ink">Catatan bengkel</h3>

                    <div class="mt-4 space-y-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Diagnosis</p>
                            <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-ink/80">
                                {{ $serviceOrder->diagnosis ?: 'Belum ada diagnosis.' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Catatan pekerjaan</p>
                            <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-ink/80">
                                {{ $serviceOrder->work_notes ?: 'Belum ada catatan pekerjaan.' }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                    <h3 class="text-base font-semibold tracking-tight text-ink">Riwayat status</h3>
                    <p class="mt-0.5 text-xs text-muted">Waktu perubahan status tercatat otomatis.</p>

                    <ol class="mt-5 space-y-4">
                        @forelse ($serviceOrder->statusLogs->sortBy('created_at') as $log)
                            <li class="relative border-l border-line pl-4 last:border-transparent">
                                <span class="absolute -left-[5px] top-1 h-2.5 w-2.5 rounded-full bg-brand-dark"></span>
                                <p class="text-xs font-semibold text-ink">{{ $log->summary() }}</p>
                                <p class="mt-1 text-[11px] text-muted">
                                    {{ $log->created_at?->timezone(config('app.display_timezone'))->translatedFormat('d M Y H:i') }}
                                </p>
                                @if ($log->notes)
                                    <p class="mt-1 text-[11px] italic text-muted">{{ $log->notes }}</p>
                                @endif
                            </li>
                        @empty
                            <li class="text-sm text-muted">Belum ada riwayat.</li>
                        @endforelse
                    </ol>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
