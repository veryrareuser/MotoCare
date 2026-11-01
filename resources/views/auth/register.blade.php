<x-guest-layout title="Daftar Akun">
    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Buat akun pelanggan</p>
    <h1 class="mt-3 text-[34px] font-semibold leading-[1.15] tracking-tight text-ink">Mulai rawat motor Anda</h1>
    <p class="mt-3 text-sm text-muted">Daftar untuk mengelola booking, servis, dan pengingat dalam satu tempat.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="name" value="Nama lengkap" />
            <x-text-input
                id="name"
                class="mt-2 block w-full"
                type="text"
                name="name"
                :value="old('name')"
                placeholder="Nama sesuai identitas"
                required
                autofocus
                autocomplete="name"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

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
            Daftar
            <x-icon name="arrow-right" class="h-4 w-4" />
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-muted">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-bold text-brand-dark hover:underline">Masuk di sini</a>
    </p>
</x-guest-layout>
