<x-app-layout title="Notifikasi">
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Kotak masuk</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Notifikasi</h2>
                <p class="mt-1 max-w-xl text-sm leading-relaxed text-muted">
                    Pengingat servis dan peringatan stok rendah dari workshop.
                </p>
            </div>

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('patch')
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-[10px] border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-canvas"
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Tandai semua dibaca
                    </button>
                </form>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('notifications.index') }}"
                class="inline-flex items-center gap-2 rounded-[10px] px-3.5 py-2 text-sm font-semibold transition {{ $filter === 'unread' ? 'text-muted hover:bg-canvas hover:text-ink' : 'bg-ink text-white' }}"
            >
                Semua
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $filter === 'unread' ? 'bg-canvas text-muted' : 'bg-white/15 text-white' }}">
                    {{ $totalCount }}
                </span>
            </a>

            <a
                href="{{ route('notifications.index', ['filter' => 'unread']) }}"
                class="inline-flex items-center gap-2 rounded-[10px] px-3.5 py-2 text-sm font-semibold transition {{ $filter === 'unread' ? 'bg-ink text-white' : 'text-muted hover:bg-canvas hover:text-ink' }}"
            >
                Belum dibaca
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $filter === 'unread' ? 'bg-white/15 text-white' : 'bg-warm text-[#8A5A00]' }}">
                    {{ $unreadCount }}
                </span>
            </a>
        </div>

        @if ($notifications->isEmpty())
            <section class="rounded-2xl border border-line bg-white p-8 shadow-card sm:p-12">
                <div class="mx-auto flex max-w-md flex-col items-center text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-soft text-brand-dark">
                        <x-icon name="bell" class="h-8 w-8" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-ink">
                        {{ $filter === 'unread' ? 'Semua notifikasi sudah dibaca' : 'Belum ada notifikasi' }}
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">
                        Notifikasi pengingat servis dan stok rendah akan muncul di sini.
                    </p>
                </div>
            </section>
        @else
            <div class="space-y-3">
                @foreach ($notifications as $notification)
                    @php
                        $kind = App\Enums\NotificationKind::tryFrom($notification->data['kind'] ?? '');
                        $isUnread = $notification->read_at === null;
                    @endphp

                    <article class="rounded-2xl border bg-white p-5 shadow-card transition {{ $isUnread ? 'border-brand/40' : 'border-line' }}">
                        <div class="flex items-start gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $kind?->tone() ?? 'bg-canvas text-muted' }}">
                                <x-icon :name="$kind?->icon() ?? 'bell'" class="h-5 w-5" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-semibold text-ink">
                                        {{ $notification->data['title'] ?? 'Notifikasi' }}
                                    </h3>
                                    @if ($kind)
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $kind->tone() }}">
                                            {{ $kind->label() }}
                                        </span>
                                    @endif
                                    @if ($isUnread)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-brand-soft px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-dark">
                                            <span class="h-1.5 w-1.5 rounded-full bg-brand-dark"></span>
                                            Baru
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-2 text-sm leading-relaxed text-ink/80">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>

                                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                    <p class="text-[11px] text-muted">
                                        {{ $notification->created_at?->timezone(config('app.display_timezone'))->translatedFormat('d M Y H:i') }}
                                    </p>

                                    <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                        @csrf
                                        @method('patch')
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-2 text-xs font-semibold transition {{ $isUnread ? 'text-brand-dark hover:text-ink' : 'text-muted hover:text-ink' }}"
                                        >
                                            @if ($isUnread)
                                                Tandai dibaca &amp; buka
                                            @else
                                                Buka tautan
                                            @endif
                                            <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($notifications->hasPages())
                <div>
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
