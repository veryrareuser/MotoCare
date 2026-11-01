<x-app-layout title="Tambah Akun Staf">
    <div class="mx-auto max-w-3xl space-y-6">
        <a
            href="{{ route('staff.accounts.index') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-ink"
        >
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke akun staf
        </a>

        <div class="rounded-2xl border border-line bg-white p-6 shadow-card sm:p-8">
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Manajemen akses</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Tambah akun staf</h2>
            <p class="mt-1 text-sm leading-relaxed text-muted">
                Akun langsung aktif dan dapat masuk melalui portal staf. Bagikan kata sandi awal secara aman.
            </p>

            <form method="POST" action="{{ route('staff.accounts.store') }}" class="mt-6 space-y-5">
                @csrf

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="name" value="Nama lengkap" />
                        <x-text-input
                            id="name"
                            name="name"
                            type="text"
                            class="mt-2 block w-full"
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
                            name="email"
                            type="email"
                            class="mt-2 block w-full"
                            :value="old('email')"
                            placeholder="nama@motocare.test"
                            required
                            autocomplete="email"
                        />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="phone" value="Nomor telepon (opsional)" />
                        <x-text-input
                            id="phone"
                            name="phone"
                            type="tel"
                            class="mt-2 block w-full"
                            :value="old('phone')"
                            placeholder="08xxxxxxxxxx"
                            autocomplete="tel"
                        />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="role" value="Peran" />
                        <x-select-input id="role" name="role" class="mt-2 block w-full" required>
                            <option value="" disabled @selected(old('role') === null)>Pilih peran</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}" @selected(old('role') === $role->value)>
                                    {{ $role->label() }}
                                </option>
                            @endforeach
                        </x-select-input>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Kata sandi awal" />
                        <x-text-input
                            id="password"
                            name="password"
                            type="password"
                            class="mt-2 block w-full"
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
                            name="password_confirmation"
                            type="password"
                            class="mt-2 block w-full"
                            placeholder="Ulangi kata sandi"
                            required
                            autocomplete="new-password"
                        />
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-xl border border-line bg-canvas/60 px-4 py-3">
                    <x-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-brand-dark" />
                    <p class="text-xs leading-relaxed text-muted">
                        Peran menentukan menu dan tindakan yang dapat diakses. Akun pemilik tidak dapat dibuat dari halaman ini.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 border-t border-line pt-5">
                    <x-primary-button>Buat akun staf</x-primary-button>

                    <a
                        href="{{ route('staff.accounts.index') }}"
                        class="inline-flex items-center justify-center rounded-[10px] px-5 py-3 text-sm font-semibold text-muted transition hover:bg-canvas hover:text-ink"
                    >
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
