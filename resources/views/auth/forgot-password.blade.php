<x-guest-layout title="Lupa Kata Sandi">
    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Pemulihan akun</p>
    <h1 class="mt-3 text-[34px] font-semibold leading-[1.15] tracking-tight text-ink">Lupa kata sandi?</h1>
    <p class="mt-3 text-sm leading-relaxed text-muted">
        Masukkan alamat email Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.
    </p>

    <x-auth-session-status class="mt-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
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

        <x-primary-button class="w-full">
            Kirim tautan reset
            <x-icon name="arrow-right" class="h-4 w-4" />
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-muted">
        <a href="{{ route('login') }}" class="font-semibold text-ink hover:underline">Kembali ke halaman masuk</a>
    </p>
</x-guest-layout>
