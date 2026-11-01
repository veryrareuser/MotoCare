<x-guest-layout title="Reset Kata Sandi">
    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Pemulihan akun</p>
    <h1 class="mt-3 text-[34px] font-semibold leading-[1.15] tracking-tight text-ink">Buat kata sandi baru</h1>
    <p class="mt-3 text-sm text-muted">Gunakan kata sandi yang kuat dan belum pernah dipakai sebelumnya.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" value="Alamat email" />
            <x-text-input
                id="email"
                class="mt-2 block w-full"
                type="email"
                name="email"
                :value="old('email', $request->email)"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Kata sandi baru" />
            <x-text-input
                id="password"
                class="mt-2 block w-full"
                type="password"
                name="password"
                placeholder="Minimal 8 karakter"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi kata sandi" />
            <x-text-input
                id="password_confirmation"
                class="mt-2 block w-full"
                type="password"
                name="password_confirmation"
                placeholder="Ulangi kata sandi"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">
            Simpan kata sandi
            <x-icon name="arrow-right" class="h-4 w-4" />
        </x-primary-button>
    </form>
</x-guest-layout>
