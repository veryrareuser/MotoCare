@php
    use App\Enums\ServiceOrderStatus;

    $statuses = ServiceOrderStatus::cases();
@endphp

<x-app-layout title="Tracking Servis">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Operasional</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Tracking Servis</h2>
                <p class="mt-1 max-w-xl text-sm leading-relaxed text-muted">
                    Pantau progres motor Anda di workshop, dari antrean hingga siap diambil.
                </p>
            </div>

            <a
                href="{{ route('service-history.index') }}"
                class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-canvas"
            >
                <x-icon name="clock" class="h-4 w-4" />
                Riwayat servis
            </a>
        </div>

        @if ($serviceOrders->isEmpty())
            <section class="rounded-2xl border border-line bg-white p-8 shadow-card sm:p-12">
                <div class="mx-auto flex max-w-md flex-col items-center text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-soft text-brand-dark">
                        <x-icon name="wrench" class="h-8 w-8" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-ink">Tidak ada servis aktif</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">
                        Setelah booking dan check-in di workshop, progres servis akan tampil di sini.
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
                @foreach ($serviceOrders as $serviceOrder)
                    @php
                        $currentIndex = array_search($serviceOrder->status, $statuses, true);
                        $motorcycle = $serviceOrder->motorcycle;
                        $invoice = $serviceOrder->invoice;
                    @endphp

                    <article class="rounded-2xl border border-line bg-white p-6 shadow-card sm:p-7">
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

                                <p class="mt-3 text-sm font-semibold text-ink">
                                    {{ $motorcycle?->displayName() ?? 'Motor terhapus' }}
                                    &middot; {{ $motorcycle?->license_plate ?? 'Tanpa plat' }}
                                </p>
                                <p class="mt-0.5 text-xs text-muted">
                                    Check-in {{ $serviceOrder->checkedInAtLocal()->translatedFormat('d M Y H:i') }}
                                    &middot; Odometer {{ $serviceOrder->formattedOdometerIn() }}
                                </p>
                            </div>

                            <a
                                href="{{ route('service-history.show', $serviceOrder) }}"
                                class="inline-flex shrink-0 items-center gap-2 rounded-[10px] border border-line bg-white px-3.5 py-2 text-xs font-semibold text-ink transition hover:bg-canvas"
                            >
                                Detail servis
                                <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                            </a>
                        </div>

                        <ol class="mt-6 grid gap-3 sm:grid-cols-5">
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

                        <div class="mt-5 grid gap-4 border-t border-line pt-5 sm:grid-cols-3">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Layanan</p>
                                <p class="mt-1 text-sm font-semibold text-ink">{{ $serviceOrder->services->count() }} item</p>
                                <p class="mt-0.5 text-xs text-muted">{{ $serviceOrder->formattedServiceAmount() }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Sparepart</p>
                                <p class="mt-1 text-sm font-semibold text-ink">{{ $serviceOrder->parts->count() }} item</p>
                                <p class="mt-0.5 text-xs text-muted">{{ $serviceOrder->formattedPartAmount() }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Mekanik</p>
                                <p class="mt-1 text-sm font-semibold text-ink">
                                    {{ $serviceOrder->mechanicNames() ?: 'Belum ditugaskan' }}
                                </p>
                                <p class="mt-0.5 text-xs text-muted">
                                    Estimasi total {{ $serviceOrder->formattedTotalAmount() }}
                                </p>
                            </div>
                        </div>

                        @if ($invoice)
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-line bg-canvas/40 px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2 text-xs text-muted">
                                    <x-icon name="cash" class="h-4 w-4" />
                                    <span class="font-mono font-semibold text-ink">{{ $invoice->invoice_number }}</span>
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $invoice->payment_status->tone() }}">
                                        {{ $invoice->payment_status->label() }}
                                    </span>
                                    <span>Sisa {{ $invoice->formattedRemaining() }}</span>
                                </div>

                                <a
                                    href="{{ route('invoices.show', $invoice) }}"
                                    class="inline-flex items-center gap-2 text-xs font-semibold text-brand-dark transition hover:text-ink"
                                >
                                    Lihat invoice
                                    <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                                </a>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
