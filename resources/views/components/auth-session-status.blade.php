@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-[10px] border border-brand-soft bg-brand-soft/50 px-4 py-3 text-sm font-medium text-brand-dark']) }}>
        {{ $status }}
    </div>
@endif
