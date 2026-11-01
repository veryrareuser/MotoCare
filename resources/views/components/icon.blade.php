@props(['name' => 'dashboard', 'class' => 'h-5 w-5'])

<svg {{ $attributes->merge([
    'class' => $class,
    'viewBox' => '0 0 24 24',
    'fill' => 'none',
    'stroke' => 'currentColor',
    'stroke-width' => '1.7',
    'stroke-linecap' => 'round',
    'stroke-linejoin' => 'round',
    'aria-hidden' => 'true',
]) }}>
    @switch($name)
        @case('dashboard')
            <path d="M3 10.5 12 3l9 7.5" />
            <path d="M5.25 9.75V20a1 1 0 0 0 1 1h3.5v-5.5h4.5V21h3.5a1 1 0 0 0 1-1V9.75" />
            @break

        @case('motorcycle')
            <circle cx="5.5" cy="17.5" r="2.5" />
            <circle cx="18.5" cy="17.5" r="2.5" />
            <path d="M5.5 17.5h4.2l2.6-6.5h4.2l2 6.5" />
            <path d="M9.7 11h3.2" />
            <path d="m16.5 11 1.5-3.5h3" />
            @break

        @case('calendar')
            <rect x="3" y="4" width="18" height="17" rx="2" />
            <path d="M8 2v4M16 2v4M3 10h18" />
            @break

        @case('history')
            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
            <path d="M3 3v5h5" />
            <path d="M12 7v5l4 2" />
            @break

        @case('box')
            <path d="m21 8-9-5-9 5 9 5 9-5Z" />
            <path d="M3 8v8l9 5 9-5V8" />
            <path d="M12 13v8" />
            @break

        @case('bell')
            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 8-3 8h18s-3-1-3-8" />
            <path d="M13.7 21a2 2 0 0 1-3.4 0" />
            @break

        @case('user')
            <path d="M20 21a8 8 0 1 0-16 0" />
            <circle cx="12" cy="7" r="4" />
            @break

        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
            @break

        @case('wrench')
            <path d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75" />
            @break

        @case('clipboard')
            <path d="M9 4h6M9 4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2M9 4V3h6v1" />
            <path d="M9 10h6M9 14h6M9 18h3" />
            @break

        @case('cash')
            <rect x="3" y="6" width="18" height="12" rx="2" />
            <circle cx="12" cy="12" r="2.5" />
            <path d="M6 9v6M18 9v6" />
            @break

        @case('chart')
            <path d="M3 3v18h18" />
            <path d="M7 16v-5M12 16V8M17 16v-8" />
            @break

        @case('alert')
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
            <path d="M12 9v4M12 17h.01" />
            @break

        @case('check')
            <path d="m5 13 4 4L19 7" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l4 2" />
            @break

        @case('logout')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <path d="m16 17 5-5-5-5M21 12H9" />
            @break

        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16" />
            @break

        @case('x')
            <path d="M6 18 18 6M6 6l12 12" />
            @break

        @case('plus')
            <path d="M12 5v14M5 12h14" />
            @break

        @case('arrow-right')
            <path d="M5 12h14M13 6l6 6-6 6" />
            @break

        @case('arrow-left')
            <path d="M19 12H5M11 18l-6-6 6-6" />
            @break

        @case('chevron-down')
            <path d="m6 9 6 6 6-6" />
            @break

        @case('shield')
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
            <path d="m9 12 2 2 4-4" />
            @break

        @case('search')
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-3.5-3.5" />
            @break

        @case('sparkle')
            <path d="m12 3 2.1 5.6L20 10.7l-5.9 2.1L12 18.4l-2.1-5.6L4 10.7l5.9-2.1L12 3Z" />
            @break

        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
