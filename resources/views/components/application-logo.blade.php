@props(['class' => 'h-10 w-10'])

<svg {{ $attributes->merge(['class' => $class, 'viewBox' => '0 0 40 40', 'fill' => 'none', 'aria-hidden' => 'true']) }}>
    <rect width="40" height="40" rx="11" fill="#D7FF00" />
    <circle cx="12" cy="26" r="4" stroke="#1D1D1D" stroke-width="2.2" />
    <circle cx="28" cy="26" r="4" stroke="#1D1D1D" stroke-width="2.2" />
    <path d="M12 26h4.5L20 18h5l3 8" stroke="#1D1D1D" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
    <path d="M19.5 18h4" stroke="#1D1D1D" stroke-width="2.2" stroke-linecap="round" />
    <path d="m25 18 1.8-4H30" stroke="#1D1D1D" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
</svg>
