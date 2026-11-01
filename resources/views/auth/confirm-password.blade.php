<x-guest-layout title="Konfirmasi Kata Sandi">
    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Area aman</p>
    <h1 class="mt-3 text-[34px] font-semibold leading-[1.15] tracking-tight text-ink">Konfirmasi kata sandi</h1>
    <p class="mt-3 text-sm leading-relaxed text-muted">
        Ini adalah area aman. Masukkan kata sandi Anda untuk melanjutkan.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="password" value="Kata sandi" />
            <x-text-input
                id="password"
                class="mt-2 block w-full"
                type="password"
                name="password"
                placeholder="Kata sandi Anda"
                required
                autofocus
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">
            Konfirmasi
            <x-icon name="arrow-right" class="h-4 w-4" />
        </x-primary-button>
    </form>
</x-guest-layout>
