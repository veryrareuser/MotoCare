@php
    use App\Enums\MaintenanceReminderStatus;

    $scheduledCount = $reminders->where('status', MaintenanceReminderStatus::Scheduled)->count();
    $sentCount = $reminders->where('status', MaintenanceReminderStatus::Sent)->count();
    $completedCount = $reminders->where('status', MaintenanceReminderStatus::Completed)->count();
@endphp

<x-app-layout title="Pengingat Servis">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Perawatan</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Pengingat Servis</h2>
                <p class="mt-1 max-w-xl text-sm leading-relaxed text-muted">
                    Pengingat manual dari bengkel untuk servis berikutnya, berdasarkan tanggal, odometer, atau keduanya.
                </p>
            </div>

            <a
                href="{{ route('bookings.create') }}"
                class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-canvas"
            >
                <x-icon name="calendar" class="h-4 w-4" />
                Booking servis
            </a>
        </div>

        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Terjadwal</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $scheduledCount }}</p>
                <p class="mt-1 text-xs text-muted">Menunggu jatuh tempo</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Jatuh tempo</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $sentCount }}</p>
                <p class="mt-1 text-xs text-muted">Sudah dinotifikasi</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Selesai</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $completedCount }}</p>
                <p class="mt-1 text-xs text-muted">Sudah ditindaklanjuti</p>
            </div>
        </section>

        @if ($reminders->isEmpty())
            <section class="rounded-2xl border border-line bg-white p-8 shadow-card sm:p-12">
                <div class="mx-auto flex max-w-md flex-col items-center text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-warm text-[#8A5A00]">
                        <x-icon name="bell" class="h-8 w-8" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-ink">Belum ada pengingat</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">
                        Staf bengkel akan membuat pengingat manual setelah servis, misalnya untuk servis rutin berikutnya.
                    </p>
                    <a
                        href="{{ route('service-history.index') }}"
                        class="mt-6 inline-flex items-center gap-2 rounded-[10px] bg-brand px-5 py-3 text-sm font-bold text-ink transition hover:bg-brand-accent"
                    >
                        <x-icon name="clock" class="h-4 w-4" />
                        Lihat riwayat servis
                    </a>
                </div>
            </section>
        @else
            <div class="space-y-4">
                @foreach ($reminders as $reminder)
                    @php($motorcycle = $reminder->motorcycle)

                    <article class="rounded-2xl border border-line bg-white p-6 shadow-card">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="flex min-w-0 items-start gap-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-warm text-[#8A5A00]">
                                    <x-icon name="bell" class="h-5 w-5" />
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-semibold text-ink">{{ $reminder->title }}</h3>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $reminder->status->tone() }}">
                                            {{ $reminder->status->label() }}
                                        </span>
                                        @if ($reminder->status->isOpen() && $reminder->isDue())
                                            <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-red-600">
                                                Jatuh tempo
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-2 text-sm text-ink">
                                        {{ $motorcycle?->displayName() ?? 'Motor terhapus' }}
                                        &middot; {{ $motorcycle?->license_plate ?? 'Tanpa plat' }}
                                    </p>
                                    <p class="mt-1 text-xs text-muted">
                                        Jatuh tempo {{ $reminder->dueSummary() }}
                                    </p>
                                    @if ($reminder->description)
                                        <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-muted">{{ $reminder->description }}</p>
                                    @endif
                                </div>
                            </div>

                            <a
                                href="{{ route('reminders.show', $reminder) }}"
                                class="inline-flex shrink-0 items-center gap-2 rounded-[10px] border border-line bg-white px-3.5 py-2 text-xs font-semibold text-ink transition hover:bg-canvas"
                            >
                                Detail
                                <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
