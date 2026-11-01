@php
    $user = auth()->user();
    $items = config('navigation.'.$user->role->value, []);
@endphp

<div
    x-cloak
    x-show="sidebarOpen"
    x-transition.opacity
    class="fixed inset-0 z-30 bg-ink/50 lg:hidden"
    @click="sidebarOpen = false"
></div>

<aside
    class="fixed inset-y-0 left-0 z-40 flex w-[248px] flex-col bg-ink text-white transition-transform duration-200 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="flex h-[84px] shrink-0 items-center gap-3 border-b border-white/10 px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <x-application-logo class="h-9 w-9" />
            <span class="text-[17px] font-black tracking-tight text-white">MOTOCARE</span>
        </a>

        <button
            type="button"
            class="ml-auto rounded-lg p-2 text-white/50 hover:bg-white/10 hover:text-white lg:hidden"
            @click="sidebarOpen = false"
            aria-label="Tutup menu"
        >
            <x-icon name="x" class="h-5 w-5" />
        </button>
    </div>

    <div class="shrink-0 px-6 pt-6">
        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/40">Ruang Kerja</p>
        <p class="mt-1 text-sm font-semibold text-white">{{ $user->role->label() }}</p>
    </div>

    <nav class="mt-4 flex-1 space-y-1 overflow-y-auto px-3 pb-4">
        @foreach ($items as $item)
            @if (isset($item['section']))
                <p class="px-3 pb-1 pt-4 text-[10px] font-bold uppercase tracking-[0.18em] text-white/35 first:pt-0">
                    {{ $item['section'] }}
                </p>
            @endif

            @if (isset($item['route']))
                <a
                    href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs($item['route']) ? 'bg-brand text-ink' : 'text-white/70 hover:bg-white/10 hover:text-white' }}"
                >
                    <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @else
                <span
                    class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-white/35"
                    title="Tersedia pada fase berikutnya"
                >
                    <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                    <span>{{ $item['label'] }}</span>
                    <span class="ml-auto rounded-full bg-white/5 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white/30">
                        Segera
                    </span>
                </span>
            @endif
        @endforeach
    </nav>

    <div class="shrink-0 px-4 pb-4">
        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand/15 text-brand">
                <x-icon name="check" class="h-4 w-4" />
            </div>
            <p class="mt-3 text-sm font-semibold text-white">Butuh bantuan?</p>
            <p class="mt-1 text-xs leading-relaxed text-white/45">Hubungi admin melalui menu profil bila Anda mengalami kendala.</p>
        </div>
    </div>

    <div class="shrink-0 border-t border-white/10 p-4">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10 text-xs font-bold text-white">
                {{ $user->initials() }}
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                <p class="truncate text-xs text-white/45">{{ $user->role->label() }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="ml-auto">
                @csrf
                <button
                    type="submit"
                    class="rounded-lg p-2 text-white/50 transition hover:bg-white/10 hover:text-white"
                    title="Keluar"
                >
                    <x-icon name="logout" class="h-5 w-5" />
                </button>
            </form>
        </div>
    </div>
</aside>
