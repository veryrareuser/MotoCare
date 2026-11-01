<x-guest-layout title="Masuk Pelanggan">
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Selamat datang kembali</p>
    <h1 class="mt-3 text-[34px] font-semibold leading-[1.15] tracking-tight text-ink">
        Masuk sebagai<br>Pelanggan
    </h1>
    <p class="mt-3 text-sm text-muted">Kelola servis kendaraan Anda dengan lebih mudah.</p>

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
            Masuk
            <x-icon name="arrow-right" class="h-4 w-4" />
        </x-primary-button>
    </form>

    <div class="my-7 flex items-center gap-3 text-xs text-muted">
        <span class="h-px flex-1 bg-line"></span>
        atau
        <span class="h-px flex-1 bg-line"></span>
    </div>

    <p class="text-center text-sm text-muted">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-bold text-brand-dark hover:underline">Daftar sekarang</a>
    </p>

    <p class="mt-6 text-center text-xs text-muted/80">
        Staf MotoCare?
        <a href="{{ route('login.staff') }}" class="font-semibold text-ink hover:underline">Masuk lewat portal staf</a>
    </p>
</x-guest-layout>
