@php
    $canManage = auth()->user()->can('create', App\Models\MaintenanceReminder::class);
    $scheduledCount = (int) ($counts[App\Enums\MaintenanceReminderStatus::Scheduled->value] ?? 0);
    $sentCount = (int) ($counts[App\Enums\MaintenanceReminderStatus::Sent->value] ?? 0);
    $completedCount = (int) ($counts[App\Enums\MaintenanceReminderStatus::Completed->value] ?? 0);
@endphp

<x-app-layout title="Pengingat Servis">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Perawatan</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Pengingat Servis</h2>
                <p class="mt-1 max-w-2xl text-sm leading-relaxed text-muted">
                    Pengingat dibuat manual berdasarkan tanggal, odometer, atau keduanya. Notifikasi dikirim otomatis saat jatuh tempo.
                </p>
            </div>

            @if ($canManage)
                <a
                    href="{{ route('staff.reminders.create') }}"
                    class="inline-flex items-center gap-2 rounded-[10px] bg-brand px-4 py-2.5 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Pengingat
                </a>
            @endif
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Total pengingat</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $reminders->count() }}</p>
                <p class="mt-1 text-xs text-muted">Sesuai filter saat ini</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Terjadwal</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $scheduledCount }}</p>
                <p class="mt-1 text-xs text-muted">Menunggu jatuh tempo</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Terkirim</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $sentCount }}</p>
                <p class="mt-1 text-xs text-muted">Sudah dinotifikasi</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Selesai</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $completedCount }}</p>
                <p class="mt-1 text-xs text-muted">Sudah ditindaklanjuti</p>
            </div>
        </section>

        <form method="GET" action="{{ route('staff.reminders.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-[240px] flex-1">
                <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                <x-text-input
                    name="search"
                    type="search"
                    class="block w-full pl-10"
                    :value="$search"
                    placeholder="Cari judul, pelanggan, atau plat"
                />
            </div>

            <x-select-input name="status" class="min-w-[190px]">
                <option value="">Semua status</option>
                @foreach (App\Enums\MaintenanceReminderStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($status === $case)>{{ $case->label() }}</option>
                @endforeach
            </x-select-input>

            <x-secondary-button type="submit">Terapkan</x-secondary-button>

            @if ($search !== '' || $status !== null)
                <a href="{{ route('staff.reminders.index') }}" class="text-sm font-semibold text-muted transition hover:text-ink">
                    Reset
                </a>
            @endif
        </form>

        <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-left">
                    <thead class="border-b border-line bg-canvas/60">
                        <tr class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
                            <th class="px-6 py-4 font-bold">Pengingat</th>
                            <th class="px-6 py-4 font-bold">Pelanggan & motor</th>
                            <th class="px-6 py-4 font-bold">Jatuh tempo</th>
                            <th class="px-6 py-4 font-bold">Status</th>
                            <th class="px-6 py-4 font-bold">Dibuat</th>
                            <th class="px-6 py-4 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($reminders as $reminder)
                            <tr class="transition hover:bg-canvas/40">
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-warm text-[#8A5A00]">
                                            <x-icon name="bell" class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-ink">{{ $reminder->title }}</p>
                                            @if ($reminder->description)
                                                <p class="mt-0.5 line-clamp-1 text-xs text-muted">{{ $reminder->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-ink">{{ $reminder->motorcycle?->displayName() ?? 'Motor terhapus' }}</p>
                                    <p class="mt-0.5 text-xs text-muted">
                                        {{ $reminder->motorcycle?->license_plate ?? 'Tanpa plat' }}
                                        &middot; {{ $reminder->motorcycle?->customer?->name ?? 'Pelanggan' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm text-ink">{{ $reminder->dueSummary() }}</p>
                                    @if ($reminder->isDue())
                                        <span class="mt-1 inline-flex rounded-full bg-warm px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-[#8A5A00]">
                                            Jatuh tempo
                                        </span>
                                    @elseif ($reminder->isOverdue())
                                        <span class="mt-1 inline-flex rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-red-600">
                                            Terlewat
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $reminder->status->tone() }}">
                                        {{ $reminder->status->label() }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-xs font-semibold text-ink">{{ $reminder->createdBy?->name ?? 'Sistem' }}</p>
                                    <p class="mt-0.5 text-[11px] text-muted">
                                        {{ $reminder->created_at?->timezone(config('app.display_timezone'))->translatedFormat('d M Y') }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            href="{{ route('staff.reminders.show', $reminder) }}"
                                            class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-3.5 py-2 text-xs font-semibold text-ink transition hover:bg-canvas"
                                        >
                                            Detail
                                        </a>

                                        @can('update', $reminder)
                                            <a
                                                href="{{ route('staff.reminders.edit', $reminder) }}"
                                                class="inline-flex items-center gap-2 rounded-[10px] bg-ink px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-ink/90"
                                            >
                                                Edit
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-soft text-brand-dark">
                                            <x-icon name="bell" class="h-6 w-6" />
                                        </div>
                                        <p class="mt-4 text-sm font-semibold text-ink">Belum ada pengingat</p>
                                        <p class="mt-1 text-sm text-muted">
                                            Buat pengingat manual agar pelanggan tahu kapan servis berikutnya.
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
