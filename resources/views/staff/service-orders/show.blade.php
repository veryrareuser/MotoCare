@php
    use App\Enums\MechanicAssignmentRole;

    $canUpdateStatus = auth()->user()->can('updateStatus', $serviceOrder);
    $canAssignMechanic = auth()->user()->can('assignMechanic', $serviceOrder);
    $canRecordService = auth()->user()->can('recordService', $serviceOrder);
    $canRecordPart = auth()->user()->can('recordPart', $serviceOrder);
    $isLocked = $serviceOrder->isLocked();
    $nextStatus = old('status', $allowedStatuses[0]->value ?? '');
@endphp

<x-app-layout :title="'Work Order '.$serviceOrder->work_order_number">
    <div class="mx-auto max-w-6xl space-y-6">
        <a
            href="{{ route('staff.service-orders.index', ['date' => $serviceOrder->checkedInAtLocal()?->toDateString()]) }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-ink"
        >
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke work order
        </a>

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-ink text-white">
                    <x-icon name="wrench" class="h-7 w-7" />
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Work order</p>
                    <h2 class="mt-1 font-mono text-2xl font-semibold tracking-tight text-ink">{{ $serviceOrder->work_order_number }}</h2>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $serviceOrder->status->tone() }}">
                            {{ $serviceOrder->status->label() }}
                        </span>
                        <span class="text-sm text-muted">{{ $serviceOrder->customer?->name ?? 'Pelanggan' }}</span>
                        <span class="text-muted">&middot;</span>
                        <span class="text-sm text-muted">{{ $serviceOrder->queueLabel() }}</span>
                    </div>
                </div>
            </div>

            @if ($serviceOrder->booking)
                <a
                    href="{{ route('staff.bookings.show', $serviceOrder->booking) }}"
                    class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-canvas"
                >
                    <x-icon name="clipboard" class="h-4 w-4" />
                    Lihat antrean {{ $serviceOrder->booking->queue_number }}
                </a>
            @endif
        </div>

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-5">
                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Motor</p>
                        <p class="mt-2 text-sm font-semibold text-ink">{{ $serviceOrder->motorcycle?->displayName() ?? 'Motor terhapus' }}</p>
                        <p class="mt-1 text-xs text-muted">{{ $serviceOrder->motorcycle?->license_plate ?? 'Tanpa plat' }}</p>
                    </div>

                    <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Odometer masuk</p>
                        <p class="mt-2 text-sm font-semibold text-ink">{{ $serviceOrder->formattedOdometerIn() }}</p>
                        <p class="mt-1 text-xs text-muted">
                            Keluar: {{ $serviceOrder->formattedOdometerOut() }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Kontak</p>
                        <p class="mt-2 text-sm font-semibold text-ink">{{ $serviceOrder->customer?->phone ?? '—' }}</p>
                        <p class="mt-1 truncate text-xs text-muted">{{ $serviceOrder->customer?->email }}</p>
                    </div>

                    <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Masuk</p>
                        <p class="mt-2 text-sm font-semibold text-ink">
                            {{ $serviceOrder->checkedInAtLocal()?->translatedFormat('d M Y') }}
                        </p>
                        <p class="mt-1 text-xs text-muted">{{ $serviceOrder->checkedInAtLocal()?->format('H:i') }} WIB</p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
                    <div class="flex items-center justify-between gap-3 border-b border-line px-6 py-4">
                        <div>
                            <h3 class="text-base font-semibold tracking-tight text-ink">Layanan</h3>
                            <p class="mt-0.5 text-xs text-muted">Harga mengikuti salinan saat layanan dicatat.</p>
                        </div>
                        <span class="rounded-full border border-line bg-canvas/60 px-3 py-1 text-xs font-semibold text-muted">
                            {{ $serviceOrder->services->count() }} layanan
                        </span>
                    </div>

                    <div class="divide-y divide-line">
                        @forelse ($serviceOrder->services as $service)
                            <div class="flex items-start justify-between gap-4 px-6 py-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $service->description }}</p>
                                    <p class="mt-0.5 text-xs text-muted">
                                        {{ $service->formattedQuantity() }} &times; {{ $service->formattedPrice() }}
                                        &middot; {{ $service->formattedDuration() }}
                                    </p>
                                </div>
                                <div class="flex shrink-0 items-center gap-3">
                                    <p class="text-sm font-semibold text-ink">{{ $service->formattedLineTotal() }}</p>
                                    @if ($canRecordService && ! $isLocked)
                                        <form method="POST" action="{{ route('staff.service-orders.services.destroy', [$serviceOrder, $service]) }}">
                                            @csrf
                                            @method('delete')
                                            <button
                                                type="submit"
                                                class="rounded-lg border border-line px-2.5 py-1 text-[11px] font-semibold text-muted transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="px-6 py-8 text-center text-sm text-muted">Belum ada layanan dicatat.</p>
                        @endforelse
                    </div>

                    @if ($canRecordService && ! $isLocked && $serviceTypes->isNotEmpty())
                        <form method="POST" action="{{ route('staff.service-orders.services.store', $serviceOrder) }}" class="border-t border-line bg-canvas/40 px-6 py-4">
                            @csrf
                            <div class="flex flex-wrap items-end gap-3">
                                <div class="min-w-[220px] flex-1">
                                    <x-input-label for="service_type_id" value="Tambah layanan" />
                                    <x-select-input id="service_type_id" name="service_type_id" class="mt-2 block w-full">
                                        <option value="">Pilih layanan</option>
                                        @foreach ($serviceTypes as $serviceType)
                                            <option value="{{ $serviceType->id }}" @selected(old('service_type_id') == $serviceType->id)>
                                                {{ $serviceType->name }} — {{ $serviceType->formattedPrice() }}
                                            </option>
                                        @endforeach
                                    </x-select-input>
                                    <x-input-error :messages="$errors->get('service_type_id')" class="mt-2" />
                                </div>

                                <div class="w-24">
                                    <x-input-label for="quantity" value="Jumlah" />
                                    <x-text-input
                                        id="quantity"
                                        name="quantity"
                                        type="number"
                                        min="1"
                                        max="999"
                                        step="1"
                                        class="mt-2 block w-full"
                                        :value="old('quantity', 1)"
                                    />
                                    <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                                </div>

                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-2 rounded-[10px] bg-brand px-4 py-2.5 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent"
                                >
                                    <x-icon name="plus" class="h-4 w-4" />
                                    Tambah
                                </button>
                            </div>
                        </form>
                    @endif
                </section>

                <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
                    <div class="flex items-center justify-between gap-3 border-b border-line px-6 py-4">
                        <div>
                            <h3 class="text-base font-semibold tracking-tight text-ink">Sparepart terpakai</h3>
                            <p class="mt-0.5 text-xs text-muted">Penambahan otomatis memotong stok dan tercatat di ledger.</p>
                        </div>
                        <span class="rounded-full border border-line bg-canvas/60 px-3 py-1 text-xs font-semibold text-muted">
                            {{ $serviceOrder->parts->count() }} item
                        </span>
                    </div>

                    <div class="divide-y divide-line">
                        @forelse ($serviceOrder->parts as $part)
                            <div class="flex items-start justify-between gap-4 px-6 py-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $part->sparepart?->name ?? 'Sparepart terhapus' }}</p>
                                    <p class="mt-0.5 text-xs text-muted">
                                        {{ $part->sparepart?->sku ?? '—' }}
                                        &middot; {{ $part->formattedQuantity() }} &times; {{ $part->formattedPrice() }}
                                    </p>
                                </div>
                                <div class="flex shrink-0 items-center gap-3">
                                    <p class="text-sm font-semibold text-ink">{{ $part->formattedLineTotal() }}</p>
                                    @if ($canRecordPart && ! $isLocked)
                                        <form method="POST" action="{{ route('staff.service-orders.parts.destroy', [$serviceOrder, $part]) }}">
                                            @csrf
                                            @method('delete')
                                            <button
                                                type="submit"
                                                class="rounded-lg border border-line px-2.5 py-1 text-[11px] font-semibold text-muted transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="px-6 py-8 text-center text-sm text-muted">Belum ada sparepart terpakai.</p>
                        @endforelse
                    </div>

                    @if ($canRecordPart && ! $isLocked && $spareparts->isNotEmpty())
                        <form method="POST" action="{{ route('staff.service-orders.parts.store', $serviceOrder) }}" class="border-t border-line bg-canvas/40 px-6 py-4">
                            @csrf
                            <div class="flex flex-wrap items-end gap-3">
                                <div class="min-w-[220px] flex-1">
                                    <x-input-label for="sparepart_id" value="Tambah sparepart" />
                                    <x-select-input id="sparepart_id" name="sparepart_id" class="mt-2 block w-full">
                                        <option value="">Pilih sparepart</option>
                                        @foreach ($spareparts as $sparepart)
                                            <option value="{{ $sparepart->id }}" @selected(old('sparepart_id') == $sparepart->id)>
                                                {{ $sparepart->name }} ({{ $sparepart->sku }}) — stok {{ $sparepart->formattedStock() }}
                                            </option>
                                        @endforeach
                                    </x-select-input>
                                    <x-input-error :messages="$errors->get('sparepart_id')" class="mt-2" />
                                </div>

                                <div class="w-24">
                                    <x-input-label for="part_quantity" value="Jumlah" />
                                    <x-text-input
                                        id="part_quantity"
                                        name="part_quantity"
                                        type="number"
                                        min="1"
                                        max="9999"
                                        step="1"
                                        class="mt-2 block w-full"
                                        :value="old('part_quantity', 1)"
                                    />
                                    <x-input-error :messages="$errors->get('part_quantity')" class="mt-2" />
                                </div>

                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-2 rounded-[10px] bg-brand px-4 py-2.5 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent"
                                >
                                    <x-icon name="plus" class="h-4 w-4" />
                                    Tambah
                                </button>
                            </div>
                        </form>
                    @elseif ($canRecordPart && ! $isLocked)
                        <p class="border-t border-line bg-canvas/40 px-6 py-4 text-xs text-muted">
                            Tidak ada sparepart aktif dengan stok tersedia.
                        </p>
                    @endif
                </section>

                @if ($serviceOrder->work_notes || $serviceOrder->diagnosis)
                    <section class="rounded-2xl border border-line bg-white p-5 shadow-card">
                        @if ($serviceOrder->work_notes)
                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Catatan</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-ink/80">{{ $serviceOrder->work_notes }}</p>
                        @endif

                        @if ($serviceOrder->diagnosis)
                            <p class="mt-4 text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Diagnosis</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-ink/80">{{ $serviceOrder->diagnosis }}</p>
                        @endif
                    </section>
                @endif

                <section class="grid gap-4 rounded-2xl border border-line bg-white p-5 shadow-card sm:grid-cols-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Total layanan</p>
                        <p class="mt-2 text-lg font-bold tracking-tight text-ink">{{ $serviceOrder->formattedServiceAmount() }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Total sparepart</p>
                        <p class="mt-2 text-lg font-bold tracking-tight text-ink">{{ $serviceOrder->formattedPartAmount() }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Total sementara</p>
                        <p class="mt-2 text-lg font-bold tracking-tight text-ink">{{ $serviceOrder->formattedTotalAmount() }}</p>
                    </div>
                </section>
            </div>

            <aside class="space-y-5">
                @if ($canUpdateStatus && $allowedStatuses !== [])
                    <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                        <h3 class="text-base font-semibold tracking-tight text-ink">Perbarui status</h3>
                        <p class="mt-0.5 text-xs text-muted">Alur status berurutan dan tidak dapat dilompati.</p>

                        <form
                            method="POST"
                            action="{{ route('staff.service-orders.status.update', $serviceOrder) }}"
                            class="mt-4 space-y-4"
                            x-data="{ nextStatus: '{{ $nextStatus }}' }"
                        >
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label for="status" value="Status berikutnya" />
                                <x-select-input id="status" name="status" class="mt-2 block w-full" x-model="nextStatus">
                                    @foreach ($allowedStatuses as $case)
                                        <option value="{{ $case->value }}" @selected($nextStatus === $case->value)>{{ $case->label() }}</option>
                                    @endforeach
                                </x-select-input>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>

                            <div x-show="nextStatus === 'completed'" x-cloak>
                                <x-input-label for="odometer_out" value="Odometer keluar (km)" />
                                <x-text-input
                                    id="odometer_out"
                                    name="odometer_out"
                                    type="number"
                                    min="0"
                                    max="9999999"
                                    class="mt-2 block w-full"
                                    :value="old('odometer_out')"
                                    x-bind:required="nextStatus === 'completed'"
                                    placeholder="Contoh: 24620"
                                />
                                <x-input-error :messages="$errors->get('odometer_out')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="status_notes" value="Catatan (opsional)" />
                                <textarea
                                    id="status_notes"
                                    name="notes"
                                    rows="2"
                                    class="mt-2 block w-full rounded-[10px] border-line bg-white px-4 py-3 text-sm text-ink shadow-sm placeholder:text-muted/60 focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/25"
                                    placeholder="Contoh: servis selesai, motor siap diambil."
                                >{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-[10px] bg-ink px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-ink/90"
                            >
                                <x-icon name="check" class="h-4 w-4" />
                                Simpan status
                            </button>
                        </form>
                    </section>
                @elseif ($isLocked)
                    <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                        <h3 class="text-base font-semibold tracking-tight text-ink">Work order selesai</h3>
                        <p class="mt-1 text-sm leading-relaxed text-muted">
                            {{ $serviceOrder->status === App\Enums\ServiceOrderStatus::PickedUp
                                ? 'Motor sudah diambil pelanggan. Work order ditutup.'
                                : 'Servis selesai. Tandai sudah diambil setelah pelanggan menerima motor.' }}
                        </p>
                    </section>
                @endif

                <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold tracking-tight text-ink">Mekanik</h3>
                            <p class="mt-0.5 text-xs text-muted">Bisa lebih dari satu mekanik.</p>
                        </div>
                        <span class="rounded-full border border-line bg-canvas/60 px-2.5 py-1 text-[11px] font-semibold text-muted">
                            {{ $serviceOrder->activeMechanicAssignments->count() }}
                        </span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($serviceOrder->activeMechanicAssignments as $assignment)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-line bg-canvas/30 px-3.5 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $assignment->mechanic?->name ?? 'Mekanik terhapus' }}</p>
                                    <p class="mt-0.5 text-[11px] text-muted">{{ $assignment->roleLabel() }}</p>
                                </div>

                                @if ($canAssignMechanic && ! $isLocked)
                                    <form method="POST" action="{{ route('staff.service-orders.mechanics.destroy', [$serviceOrder, $assignment]) }}">
                                        @csrf
                                        @method('delete')
                                        <button
                                            type="submit"
                                            class="rounded-lg border border-line px-2.5 py-1 text-[11px] font-semibold text-muted transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                        >
                                            Lepas
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="rounded-xl border border-dashed border-line bg-canvas/30 px-4 py-5 text-center text-sm text-muted">
                                Belum ada mekanik ditugaskan.
                            </p>
                        @endforelse
                    </div>

                    @if ($canAssignMechanic && ! $isLocked && $availableMechanics->isNotEmpty())
                        <form method="POST" action="{{ route('staff.service-orders.mechanics.store', $serviceOrder) }}" class="mt-4 space-y-3 border-t border-line pt-4">
                            @csrf

                            <div>
                                <x-input-label for="mechanic_id" value="Tugaskan mekanik" />
                                <x-select-input id="mechanic_id" name="mechanic_id" class="mt-2 block w-full">
                                    <option value="">Pilih mekanik</option>
                                    @foreach ($availableMechanics as $mechanic)
                                        <option value="{{ $mechanic->id }}" @selected(old('mechanic_id') == $mechanic->id)>{{ $mechanic->name }}</option>
                                    @endforeach
                                </x-select-input>
                                <x-input-error :messages="$errors->get('mechanic_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="assignment_role" value="Peran" />
                                <x-select-input id="assignment_role" name="assignment_role" class="mt-2 block w-full">
                                    <option value="">Mekanik</option>
                                    @foreach (MechanicAssignmentRole::cases() as $case)
                                        <option value="{{ $case->value }}" @selected(old('assignment_role') === $case->value)>{{ $case->label() }}</option>
                                    @endforeach
                                </x-select-input>
                                <x-input-error :messages="$errors->get('assignment_role')" class="mt-2" />
                            </div>

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-[10px] border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-canvas"
                            >
                                <x-icon name="plus" class="h-4 w-4" />
                                Tugaskan
                            </button>
                        </form>
                    @endif
                </section>

                <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
                    <h3 class="text-base font-semibold tracking-tight text-ink">Riwayat status</h3>
                    <p class="mt-0.5 text-xs text-muted">Aktor dan waktu tercatat otomatis.</p>

                    <ol class="mt-5 space-y-4">
                        @forelse ($serviceOrder->statusLogs->sortBy('created_at') as $log)
                            <li class="relative border-l border-line pl-4 last:border-transparent">
                                <span class="absolute -left-[5px] top-1 h-2.5 w-2.5 rounded-full bg-brand-dark"></span>
                                <p class="text-xs font-semibold text-ink">{{ $log->summary() }}</p>
                                <p class="mt-1 text-[11px] text-muted">
                                    {{ $log->created_at?->timezone(config('app.display_timezone'))->translatedFormat('d M Y H:i') }}
                                    &middot; {{ $log->changedBy?->name ?? 'Sistem' }}
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
