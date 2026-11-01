@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge([
    'class' => 'rounded-[10px] border-line bg-white px-4 py-3 text-sm text-ink shadow-sm placeholder:text-muted/60 focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/25 disabled:cursor-not-allowed disabled:bg-canvas disabled:text-muted',
]) }}>
