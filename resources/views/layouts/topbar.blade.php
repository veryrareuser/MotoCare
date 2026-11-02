@php
    $user = auth()->user();
    $unreadNotificationCount = $user?->unreadNotifications()->count() ?? 0;
    $latestNotifications = $user?->notifications()->latest()->limit(5)->get() ?? collect();
@endphp

<header class="sticky top-0 z-20 flex h-[84px] shrink-0 items-center justify-between gap-4 border-b border-line bg-white px-5 sm:px-8 lg:px-[34px]">
    <div class="flex min-w-0 items-center gap-3">
        <button
            type="button"
            class="rounded-lg p-2 text-ink/70 hover:bg-canvas lg:hidden"
            @click="sidebarOpen = true"
            aria-label="Buka menu"
        >
            <x-icon name="menu" class="h-5 w-5" />
        </button>

        <div class="min-w-0">
            <p class="truncate text-[11px] font-medium text-muted">
                {{ now()->timezone(config('app.display_timezone'))->locale('id')->translatedFormat('l, d F Y') }}
            </p>
            <h1 class="mt-0.5 truncate text-xl font-semibold tracking-tight text-ink">{{ $title ?? 'Dashboard' }}</h1>
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-3 sm:gap-5">
        <div x-data="{ notificationsOpen: false }" class="relative">
            <button
                type="button"
                class="relative flex h-10 w-10 items-center justify-center rounded-full border border-line text-ink/70 transition hover:bg-canvas"
                title="Notifikasi"
                :aria-expanded="notificationsOpen"
                @click="notificationsOpen = ! notificationsOpen"
            >
                <x-icon name="bell" class="h-5 w-5" />

                @if ($unreadNotificationCount > 0)
                    <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-brand px-1 text-[10px] font-bold text-ink">
                        {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                    </span>
                @endif
            </button>

            <div
                x-cloak
                x-show="notificationsOpen"
                x-transition
                @click.outside="notificationsOpen = false"
                class="absolute right-0 mt-2 w-80 overflow-hidden rounded-2xl border border-line bg-white shadow-lg"
            >
                <div class="flex items-center justify-between gap-3 border-b border-line px-4 py-3">
                    <p class="text-sm font-semibold text-ink">Notifikasi</p>

                    @if ($unreadNotificationCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            @method('patch')
                            <button type="submit" class="text-[11px] font-semibold text-brand-dark hover:underline">
                                Tandai semua
                            </button>
                        </form>
                    @endif
                </div>

                <div class="max-h-80 divide-y divide-line overflow-y-auto">
                    @forelse ($latestNotifications as $notification)
                        @php($kind = App\Enums\NotificationKind::tryFrom($notification->data['kind'] ?? ''))

                        <form method="POST" action="{{ route('notifications.read', $notification) }}">
                            @csrf
                            @method('patch')
                            <button
                                type="submit"
                                class="flex w-full items-start gap-3 px-4 py-3 text-left transition hover:bg-canvas/60 {{ $notification->read_at !== null ? 'opacity-70' : '' }}"
                            >
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $kind?->tone() ?? 'bg-canvas text-muted' }}">
                                    <x-icon :name="$kind?->icon() ?? 'bell'" class="h-4 w-4" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-xs font-semibold text-ink">
                                        {{ $notification->data['title'] ?? 'Notifikasi' }}
                                    </span>
                                    <span class="mt-0.5 line-clamp-2 block text-[11px] leading-relaxed text-muted">
                                        {{ $notification->data['message'] ?? '' }}
                                    </span>
                                    <span class="mt-1 block text-[10px] text-muted">
                                        {{ $notification->created_at?->timezone(config('app.display_timezone'))->translatedFormat('d M Y H:i') }}
                                    </span>
                                </span>
                            </button>
                        </form>
                    @empty
                        <p class="px-4 py-8 text-center text-xs text-muted">Belum ada notifikasi.</p>
                    @endforelse
                </div>

                <a
                    href="{{ route('notifications.index') }}"
                    class="block border-t border-line bg-canvas/40 px-4 py-3 text-center text-xs font-semibold text-ink transition hover:bg-canvas"
                >
                    Lihat semua notifikasi
                </a>
            </div>
        </div>

        <div class="hidden h-8 w-px bg-line sm:block"></div>

        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-ink text-sm font-bold text-white">
                {{ $user->initials() }}
            </div>
            <div class="hidden sm:block">
                <p class="text-sm font-semibold leading-tight text-ink">{{ $user->name }}</p>
                <p class="text-xs text-muted">{{ $user->role->label() }}</p>
            </div>
            <x-icon name="chevron-down" class="hidden h-4 w-4 text-muted sm:block" />
        </div>
    </div>
</header>
