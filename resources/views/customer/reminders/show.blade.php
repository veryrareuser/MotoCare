@php
    $motorcycle = $reminder->motorcycle;
@endphp

<x-app-layout :title="'Pengingat '.$reminder->title">
    <div class="mx-auto max-w-4xl space-y-6">
        <a
            href="{{ route('reminders.index') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-ink"
        >
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke pengingat
        </a>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-warm text-[#8A5A00]">
                    <x-icon name="bell" class="h-6 w-6" />
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Pengingat servis</p>
                    <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink">{{ $reminder->title }}</h2>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $reminder->status->tone() }}">
                            {{ $reminder->status->label() }}
                        </span>
                        <span class="text-sm text-muted">{{ $motorcycle?->displayName() ?? 'Motor terhapus' }}</span>
                        <span class="text-muted">&middot;</span>
                        <span class="text-sm text-muted">{{ $motorcycle?->license_plate ?? 'Tanpa plat' }}</span>
                    </div>
                </div>
            </div>

            <a
                href="{{ route('bookings.create') }}"
                class="inline-flex items-center gap-2 rounded-[10px] bg-brand px-4 py-2.5 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent"
            >
                <x-icon name="calendar" class="h-4 w-4" />
                Booking servis
            </a>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Tanggal jatuh tempo</p>
                <p class="mt-2 text-sm font-semibold text-ink">{{ $reminder->formattedDueDate() }}</p>
                <p class="mt-1 text-xs text-muted">Patokan tanggal</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Odometer jatuh tempo</p>
                <p class="mt-2 text-sm font-semibold text-ink">{{ $reminder->formattedDueOdometer() }}</p>
                <p class="mt-1 text-xs text-muted">
                    Odometer saat ini: {{ number_format($motorcycle?->odometer_current ?? 0, 0, ',', '.') }} km
                </p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Dibuat oleh</p>
                <p class="mt-2 text-sm font-semibold text-ink">{{ $reminder->createdBy?->name ?? 'Bengkel' }}</p>
                <p class="mt-1 text-xs text-muted">
                    {{ $reminder->created_at?->timezone(config('app.display_timezone'))->translatedFormat('d M Y') }}
                </p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Dinotifikasi</p>
                <p class="mt-2 text-sm font-semibold text-ink">{{ $reminder->formattedSentAt() }}</p>
                <p class="mt-1 text-xs text-muted">Notifikasi in-app</p>
            </div>
        </section>

        <section class="rounded-2xl border border-line bg-white p-6 shadow-card">
            <h3 class="text-base font-semibold tracking-tight text-ink">Detail pengingat</h3>
            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-ink/80">
                {{ $reminder->description ?: 'Tidak ada deskripsi tambahan dari bengkel.' }}
            </p>

            <dl class="mt-5 grid gap-4 border-t border-line pt-5 sm:grid-cols-2">
                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Patokan jatuh tempo</dt>
                    <dd class="mt-1 text-sm font-semibold text-ink">{{ $reminder->dueSummary() }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">Motor</dt>
                    <dd class="mt-1 text-sm font-semibold text-ink">
                        {{ $motorcycle?->displayName() ?? 'Motor terhapus' }} &middot; {{ $motorcycle?->license_plate ?? 'Tanpa plat' }}
                    </dd>
                </div>
            </dl>

            <div class="mt-5 rounded-xl border border-line bg-canvas/40 px-4 py-3 text-xs leading-relaxed text-muted">
                Pengingat ini dibuat manual oleh staf bengkel, bukan prediksi otomatis jadwal servis.
                Notifikasi dikirim saat tanggal atau odometer jatuh tempo tercapai.
            </div>
        </section>
    </div>
</x-app-layout>
