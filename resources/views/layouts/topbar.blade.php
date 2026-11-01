@php
    $user = auth()->user();
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
        <button
            type="button"
            class="relative flex h-10 w-10 items-center justify-center rounded-full border border-line text-ink/70 transition hover:bg-canvas"
            title="Notifikasi"
        >
            <x-icon name="bell" class="h-5 w-5" />
        </button>

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
