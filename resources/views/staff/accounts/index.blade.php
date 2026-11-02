<x-app-layout title="Akun Staf">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Manajemen akses</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Akun Staf</h2>
                <p class="mt-1 max-w-xl text-sm leading-relaxed text-muted">
                    Kelola akses admin, mekanik, staf inventaris, dan kasir. Akun yang dinonaktifkan tetap menyimpan riwayat kerja.
                </p>
            </div>

            @can('create', App\Models\User::class)
                <a
                    href="{{ route('staff.accounts.create') }}"
                    class="inline-flex items-center gap-2 rounded-[10px] bg-brand px-4 py-2.5 text-sm font-bold text-ink shadow-sm transition hover:bg-brand-accent"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Staf
                </a>
            @endcan
        </div>

        @if (auth()->user()->role === App\Enums\UserRole::Owner)
            <div class="flex items-start gap-3 rounded-xl border border-line bg-white px-4 py-3 text-sm text-muted">
                <x-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-brand-dark" />
                <p>Mode baca saja: Anda dapat melihat daftar akun tanpa mengubahnya.</p>
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="border-b border-line bg-canvas/60">
                        <tr class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
                            <th class="px-6 py-4 font-bold">Nama</th>
                            <th class="px-6 py-4 font-bold">Peran</th>
                            <th class="px-6 py-4 font-bold">Status</th>
                            <th class="px-6 py-4 font-bold">Bergabung</th>
                            <th class="px-6 py-4 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($accounts as $account)
                            <tr class="transition hover:bg-canvas/40">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-ink text-xs font-bold text-white">
                                            {{ $account->initials() }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-ink">{{ $account->name }}</p>
                                            <p class="truncate text-xs text-muted">{{ $account->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="text-sm text-ink/80">{{ $account->role->label() }}</span>
                                </td>

                                <td class="px-6 py-4">
                                    @if ($account->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-brand-dark">
                                            <span class="h-1.5 w-1.5 rounded-full bg-brand-dark"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-canvas px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-muted">
                                            <span class="h-1.5 w-1.5 rounded-full bg-muted"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-sm text-muted">
                                    {{ $account->created_at?->timezone(config('app.display_timezone'))->translatedFormat('d M Y') }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    @can('deactivate', $account)
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-[10px] px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                            x-data=""
                                            x-on:click.prevent="$dispatch('open-modal', 'deactivate-account-{{ $account->id }}')"
                                        >
                                            Nonaktifkan
                                        </button>
                                    @elsecan('activate', $account)
                                        <form method="POST" action="{{ route('staff.accounts.activate', $account) }}" class="inline">
                                            @csrf
                                            @method('patch')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center rounded-[10px] px-3 py-2 text-xs font-semibold text-brand-dark transition hover:bg-brand-soft"
                                            >
                                                Aktifkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-muted">—</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-muted">
                                    Belum ada akun staf yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @foreach ($accounts as $account)
            @can('deactivate', $account)
                <x-modal name="deactivate-account-{{ $account->id }}" maxWidth="md" focusable>
                    <form method="POST" action="{{ route('staff.accounts.deactivate', $account) }}" class="p-6">
                        @csrf
                        @method('patch')

                        <h2 class="text-lg font-semibold tracking-tight text-ink">Nonaktifkan akun?</h2>
                        <p class="mt-1 text-sm leading-relaxed text-muted">
                            {{ $account->name }} tidak akan bisa masuk lagi sampai akun diaktifkan kembali. Riwayat kerja dan data servis tetap tersimpan.
                        </p>

                        <div class="mt-6 flex justify-end gap-3">
                            <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                            <x-danger-button>Nonaktifkan</x-danger-button>
                        </div>
                    </form>
                </x-modal>
            @endcan
        @endforeach
    </div>
</x-app-layout>
