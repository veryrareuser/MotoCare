<x-guest-layout title="Masuk Portal Staf">
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Akses khusus staf</p>
    <h1 class="mt-3 text-[34px] font-semibold leading-[1.15] tracking-tight text-ink">Masuk Portal Staf</h1>
    <p class="mt-3 text-sm leading-relaxed text-muted">
        Peran dan izin ditentukan otomatis dari akun terverifikasi.
    </p>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Alamat email" />
            <x-text-input
                id="email"
                class="mt-2 block w-full"
                type="email"
                name="email"
                :value="old('email')"
                placeholder="nama@email.com"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Kata sandi" />
            <x-text-input
                id="password"
                class="mt-2 block w-full"
                type="password"
                name="password"
                placeholder="Minimal 8 karakter"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-muted">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="rounded border-line text-brand-accent focus:ring-brand-accent"
                    name="remember"
                >
                Ingat saya
            </label>

            @if (Route::has('password.request'))
                <a
                    class="text-sm font-semibold text-ink underline decoration-line underline-offset-4 transition hover:decoration-ink"
                    href="{{ route('password.request') }}"
                >
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <x-primary-button class="w-full">
            Masuk Portal Staf
            <x-icon name="arrow-right" class="h-4 w-4" />
        </x-primary-button>
    </form>

    <div class="mt-6 flex gap-3 rounded-xl border border-line bg-canvas p-4">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-soft text-brand-dark">
            <x-icon name="shield" class="h-4 w-4" />
        </div>
        <p class="text-xs leading-relaxed text-muted">
            Tidak ada pendaftaran staf publik. Hubungi Admin bila akun Anda belum aktif.
        </p>
    </div>

    <p class="mt-6 text-center text-xs text-muted/80">
        Pelanggan?
        <a href="{{ route('login') }}" class="font-semibold text-ink hover:underline">Masuk lewat halaman pelanggan</a>
    </p>
</x-guest-layout>
