<x-guest-layout title="Verifikasi Email">
    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Verifikasi akun</p>
    <h1 class="mt-3 text-[34px] font-semibold leading-[1.15] tracking-tight text-ink">Verifikasi email Anda</h1>
    <p class="mt-3 text-sm leading-relaxed text-muted">
        Terima kasih telah mendaftar. Sebelum memulai, verifikasi alamat email melalui tautan yang baru kami kirim.
    </p>

    <x-auth-session-status class="mt-5" :status="session('status')" />

    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
            @csrf
            <x-primary-button class="w-full">Kirim ulang email</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="flex-1">
            @csrf
            <x-secondary-button class="w-full">Keluar</x-secondary-button>
        </form>
    </div>
</x-guest-layout>
