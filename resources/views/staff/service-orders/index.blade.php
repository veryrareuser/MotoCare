@php
    use App\Enums\ServiceOrderStatus;

    $total = (int) $counts->sum();
    $waiting = (int) ($counts[ServiceOrderStatus::WaitingQueue->value] ?? 0);
    $working = (int) ($counts[ServiceOrderStatus::BeingInspected->value] ?? 0)
        + (int) ($counts[ServiceOrderStatus::InProgress->value] ?? 0);
    $finished = (int) ($counts[ServiceOrderStatus::Completed->value] ?? 0)
        + (int) ($counts[ServiceOrderStatus::PickedUp->value] ?? 0);
    $canCreate = auth()->user()->can('create', App\Models\ServiceOrder::class);
@endphp

<x-app-layout title="Work Order">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Operasional</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Work Order</h2>
                <p class="mt-1 max-w-2xl text-sm leading-relaxed text-muted">
                    Pantau pengerjaan servis, tugaskan mekanik, dan catat layanan serta sparepart yang dipakai.
                </p>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <form method="GET" action="{{ route('staff.service-orders.index') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <x-input-label for="date" value="Tanggal masuk" />
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
                        <a href="{{ route('staff.service-orders.index') }}" class="text-sm font-semibold text-muted transition hover:text-ink">
                            Hari ini
                        </a>
                    @endif
                </form>

                @if ($canCreate)
                    <a
                        href="{{ route('staff.service-orders.create') }}"
                        class="inline-flex items-center gap-2 rounded-[10px] bg-brand px-4 py-2.5 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent"
                    >
                        <x-icon name="plus" class="h-4 w-4" />
                        Work Order Walk-in
                    </a>
                @endif
            </div>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Total work order</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $total }}</p>
                <p class="mt-1 text-xs text-muted">{{ $date->translatedFormat('l, d M Y') }}</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Menunggu antrean</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $waiting }}</p>
                <p class="mt-1 text-xs text-muted">Belum mulai dikerjakan</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Sedang dikerjakan</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $working }}</p>
                <p class="mt-1 text-xs text-muted">Diperiksa atau dalam perbaikan</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Selesai</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $finished }}</p>
                <p class="mt-1 text-xs text-muted">Siap diambil atau sudah diambil</p>
            </div>
        </section>

        <form method="GET" action="{{ route('staff.service-orders.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">

            <div class="relative min-w-[240px] flex-1">
                <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                <x-text-input
                    name="search"
                    type="search"
                    class="block w-full pl-10"
                    :value="$search"
                    placeholder="Cari work order, antrean, pelanggan, atau plat"
                />
            </div>

            <x-select-input name="status" class="min-w-[200px]">
                <option value="">Semua status</option>
                @foreach (ServiceOrderStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($status === $case)>{{ $case->label() }}</option>
                @endforeach
            </x-select-input>

            <x-secondary-button type="submit">Terapkan</x-secondary-button>

            @if ($search !== '' || $status !== null)
                <a href="{{ route('staff.service-orders.index', ['date' => $date->toDateString()]) }}" class="text-sm font-semibold text-muted transition hover:text-ink">
                    Reset
                </a>
            @endif
        </form>

        <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="border-b border-line bg-canvas/60">
                        <tr class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
                            <th class="px-6 py-4 font-bold">Work order</th>
                            <th class="px-6 py-4 font-bold">Pelanggan & motor</th>
                            <th class="px-6 py-4 font-bold">Mekanik</th>
                            <th class="px-6 py-4 font-bold">Item</th>
                            <th class="px-6 py-4 font-bold">Status</th>
                            <th class="px-6 py-4 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($serviceOrders as $serviceOrder)
                            <tr class="transition hover:bg-canvas/40">
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-lg bg-brand-soft px-2.5 py-1.5 font-mono text-[11px] font-bold text-brand-dark">
                                        {{ $serviceOrder->work_order_number }}
                                    </span>
                                    <p class="mt-1 text-[10px] uppercase tracking-wide text-muted">
                                        {{ $serviceOrder->queueLabel() }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-ink">{{ $serviceOrder->customer?->name ?? 'Pelanggan' }}</p>
                                    <p class="mt-0.5 text-xs text-muted">
                                        {{ $serviceOrder->motorcycle?->displayName() ?? 'Motor terhapus' }}
                                        &middot; {{ $serviceOrder->motorcycle?->license_plate ?? 'Tanpa plat' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    @if ($serviceOrder->mechanicNames() !== '')
                                        <p class="max-w-[200px] truncate text-sm text-ink/80">{{ $serviceOrder->mechanicNames() }}</p>
                                    @else
                                        <span class="text-xs text-muted">Belum ditugaskan</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm text-ink/80">
                                        {{ $serviceOrder->services_count }} layanan
                                        &middot; {{ $serviceOrder->parts_count }} sparepart
                                    </p>
                                    <p class="mt-0.5 text-xs text-muted">{{ $serviceOrder->formattedOdometerIn() }}</p>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $serviceOrder->status->tone() }}">
                                        {{ $serviceOrder->status->label() }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a
                                        href="{{ route('staff.service-orders.show', $serviceOrder) }}"
                                        class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-3 py-1.5 text-xs font-semibold text-ink transition hover:bg-canvas"
                                    >
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-soft text-brand-dark">
                                            <x-icon name="wrench" class="h-6 w-6" />
                                        </div>
                                        <p class="mt-4 text-sm font-semibold text-ink">Tidak ada work order</p>
                                        <p class="mt-1 text-sm text-muted">
                                            {{ $search !== '' || $status !== null
                                                ? 'Ubah kata kunci atau filter status.'
                                                : 'Belum ada work order pada tanggal ini.' }}
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
