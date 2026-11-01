<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold tracking-tight text-ink">Nonaktifkan akun</h2>
        <p class="mt-1 text-sm leading-relaxed text-muted">
            Akun akan dinonaktifkan dan Anda tidak dapat masuk kembali. Riwayat servis dan data transaksi tetap tersimpan.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Nonaktifkan akun</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold tracking-tight text-ink">Yakin ingin menonaktifkan akun?</h2>
            <p class="mt-1 text-sm leading-relaxed text-muted">
                Masukkan kata sandi Anda untuk mengonfirmasi. Anda akan keluar dari sesi ini dan tidak dapat masuk kembali.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Kata sandi" class="sr-only" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-3/4" placeholder="Kata sandi" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-danger-button>Nonaktifkan akun</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
