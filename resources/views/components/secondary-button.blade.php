<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center justify-center gap-2 rounded-[10px] border border-line bg-white px-5 py-3 text-sm font-semibold text-ink shadow-sm transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
]) }}>
    {{ $slot }}
</button>
