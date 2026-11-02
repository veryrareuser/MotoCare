@php
    $canManage = auth()->user()->can('create', App\Models\ServiceType::class);
    $activeCount = $serviceTypes->where('is_active', true)->count();
@endphp

<x-app-layout title="Layanan">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Katalog</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Layanan Servis</h2>
                <p class="mt-1 max-w-xl text-sm leading-relaxed text-muted">
                    Atur daftar layanan beserta harga dasar dan estimasi durasi yang dipakai saat booking.
                </p>
            </div>

            @if ($canManage)
                <a
                    href="{{ route('staff.services.create') }}"
                    class="inline-flex items-center gap-2 rounded-[10px] bg-brand px-4 py-2.5 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Layanan
                </a>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-muted">
                <x-icon name="check" class="h-4 w-4 text-brand-dark" />
                {{ $activeCount }} layanan aktif
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-muted">
                <x-icon name="wrench" class="h-4 w-4 text-ink/60" />
                {{ $serviceTypes->count() }} layanan tercatat
            </span>
        </div>

        <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="border-b border-line bg-canvas/60">
                        <tr class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
                            <th class="px-6 py-4 font-bold">Layanan</th>
                            <th class="px-6 py-4 font-bold">Harga dasar</th>
                            <th class="px-6 py-4 font-bold">Estimasi</th>
                            <th class="px-6 py-4 font-bold">Status</th>
                            <th class="px-6 py-4 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($serviceTypes as $serviceType)
                            <tr class="transition hover:bg-canvas/40">
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-brand-dark">
                                            <x-icon name="wrench" class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-ink">{{ $serviceType->name }}</p>
                                            @if ($serviceType->description)
                                                <p class="mt-0.5 line-clamp-1 text-xs text-muted">{{ $serviceType->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm font-semibold text-ink">
                                    {{ $serviceType->formattedPrice() }}
                                </td>

                                <td class="px-6 py-4 text-sm text-muted">
                                    {{ $serviceType->formattedDuration() }}
                                </td>

                                <td class="px-6 py-4">
                                    @if ($serviceType->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-brand-dark">
                                            <span class="h-1.5 w-1.5 rounded-full bg-brand-dark"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-canvas px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-muted">
                                            <span class="h-1.5 w-1.5 rounded-full bg-muted"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-right">
                                    @can('update', $serviceType)
                                        <a
                                            href="{{ route('staff.services.edit', $serviceType) }}"
                                            class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-3.5 py-2 text-xs font-semibold text-ink transition hover:bg-canvas"
                                        >
                                            Edit
                                        </a>
                                    @else
                                        <span class="text-xs text-muted">—</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-soft text-brand-dark">
                                            <x-icon name="wrench" class="h-6 w-6" />
                                        </div>
                                        <p class="mt-4 text-sm font-semibold text-ink">Belum ada layanan</p>
                                        <p class="mt-1 text-sm text-muted">Tambahkan layanan pertama agar dapat dipilih saat booking.</p>
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
