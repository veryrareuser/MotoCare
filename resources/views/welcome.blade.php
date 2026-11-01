<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'MotoCare') }} — Mitra perawatan motor Anda</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-ink antialiased" x-data="{ mobileOpen: false }">
        <header class="sticky top-0 z-30 border-b border-line/70 bg-white/90 backdrop-blur">
            <div class="mx-auto flex h-[76px] max-w-7xl items-center justify-between gap-6 px-5 sm:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <x-application-logo class="h-9 w-9" />
                    <span class="text-[17px] font-black tracking-tight text-ink">MOTOCARE</span>
                </a>

                <nav class="hidden items-center gap-8 text-sm font-medium text-muted md:flex">
                    <a href="#layanan" class="transition hover:text-ink">Layanan</a>
                    <a href="#cara-kerja" class="transition hover:text-ink">Cara Kerja</a>
                    <a href="#pengingat" class="transition hover:text-ink">Pengingat</a>
                </nav>

                <div class="hidden items-center gap-3 md:flex">
                    <a href="{{ route('login.staff') }}" class="rounded-[10px] px-4 py-2.5 text-sm font-semibold text-muted transition hover:bg-canvas hover:text-ink">
                        Portal Staf
                    </a>
                    <a href="{{ route('login') }}" class="rounded-[10px] border border-line px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-canvas">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="rounded-[10px] bg-brand px-4 py-2.5 text-sm font-bold text-ink transition hover:bg-brand-accent">
                        Daftar
                    </a>
                </div>

                <button
                    type="button"
                    class="rounded-lg p-2 text-ink/70 hover:bg-canvas md:hidden"
                    @click="mobileOpen = ! mobileOpen"
                    aria-label="Buka menu"
                >
                    <x-icon name="menu" class="h-5 w-5" />
                </button>
            </div>

            <div x-cloak x-show="mobileOpen" x-transition class="border-t border-line bg-white px-5 py-4 md:hidden">
                <div class="flex flex-col gap-2 text-sm font-medium text-muted">
                    <a href="#layanan" class="rounded-lg px-3 py-2 hover:bg-canvas" @click="mobileOpen = false">Layanan</a>
                    <a href="#cara-kerja" class="rounded-lg px-3 py-2 hover:bg-canvas" @click="mobileOpen = false">Cara Kerja</a>
                    <a href="#pengingat" class="rounded-lg px-3 py-2 hover:bg-canvas" @click="mobileOpen = false">Pengingat</a>
                    <a href="{{ route('login.staff') }}" class="rounded-lg px-3 py-2 hover:bg-canvas">Portal Staf</a>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}" class="rounded-[10px] border border-line px-4 py-2.5 text-center font-semibold text-ink">Masuk</a>
                        <a href="{{ route('register') }}" class="rounded-[10px] bg-brand px-4 py-2.5 text-center font-bold text-ink">Daftar</a>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <section class="relative overflow-hidden bg-ink text-white">
                <div class="pointer-events-none absolute -right-32 top-1/2 h-[560px] w-[560px] -translate-y-1/2 rounded-full border border-white/5"></div>
                <div class="pointer-events-none absolute -right-10 top-1/2 h-[380px] w-[380px] -translate-y-1/2 rounded-full border border-white/5"></div>

                <div class="relative mx-auto grid max-w-7xl gap-12 px-5 py-20 sm:px-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:py-28">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-brand">Rawat kendaraan, rawat perjalanan</p>
                        <h1 class="mt-5 max-w-2xl text-[44px] font-extrabold leading-[1.05] tracking-tight text-white sm:text-[58px]">
                            Mitra terpercaya untuk setiap perjalanan Anda.
                        </h1>
                        <p class="mt-6 max-w-lg text-base leading-relaxed text-white/55">
                            Kelola perawatan motor dengan lebih mudah, cepat, dan transparan — dari booking hingga riwayat servis.
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-brand px-6 py-3.5 text-sm font-bold text-ink transition hover:bg-brand-accent">
                                Mulai sekarang
                                <x-icon name="arrow-right" class="h-4 w-4" />
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-[10px] border border-white/15 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-white/5">
                                Masuk ke akun
                            </a>
                        </div>

                        <div class="mt-10 flex flex-wrap gap-x-6 gap-y-2 text-xs text-white/40">
                            <span class="inline-flex items-center gap-2"><x-icon name="check" class="h-4 w-4 text-brand" /> Booking online</span>
                            <span class="inline-flex items-center gap-2"><x-icon name="check" class="h-4 w-4 text-brand" /> Riwayat servis</span>
                            <span class="inline-flex items-center gap-2"><x-icon name="check" class="h-4 w-4 text-brand" /> Pengingat manual</span>
                        </div>
                    </div>

                    <div class="relative mx-auto flex h-72 w-72 items-center justify-center sm:h-96 sm:w-96">
                        <div class="absolute inset-0 rounded-full border border-white/5"></div>
                        <div class="absolute inset-10 rounded-full border border-white/5"></div>
                        <div class="absolute inset-20 rounded-full bg-white/[0.03]"></div>
                        <x-icon name="motorcycle" class="relative h-40 w-40 text-brand sm:h-52 sm:w-52" />
                    </div>
                </div>
            </section>

            <section id="layanan" class="mx-auto max-w-7xl px-5 py-20 sm:px-8">
                <div class="max-w-2xl">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Layanan</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight text-ink sm:text-4xl">Semua yang dibutuhkan untuk merawat motor.</h2>
                    <p class="mt-4 text-sm leading-relaxed text-muted">
                        MotoCare menyatukan booking, antrean, riwayat servis, dan pengingat dalam satu alur yang mudah dipahami.
                    </p>
                </div>

                <div class="mt-10 grid gap-5 md:grid-cols-3">
                    <article class="rounded-2xl border border-line bg-white p-6 shadow-card">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-soft text-brand-dark">
                            <x-icon name="calendar" class="h-6 w-6" />
                        </div>
                        <h3 class="mt-5 text-lg font-semibold tracking-tight text-ink">Booking &amp; antrean</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted">
                            Pilih layanan dan slot yang tersedia. Nomor antrean harian dibuat otomatis setelah booking dikonfirmasi.
                        </p>
                    </article>

                    <article class="rounded-2xl border border-line bg-white p-6 shadow-card">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-canvas text-ink/70">
                            <x-icon name="history" class="h-6 w-6" />
                        </div>
                        <h3 class="mt-5 text-lg font-semibold tracking-tight text-ink">Riwayat transparan</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted">
                            Lihat layanan yang dikerjakan, sparepart yang dipakai, dan rincian biaya dalam satu riwayat.
                        </p>
                    </article>

                    <article class="rounded-2xl border border-line bg-white p-6 shadow-card">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-warm text-[#8A5A00]">
                            <x-icon name="bell" class="h-6 w-6" />
                        </div>
                        <h3 class="mt-5 text-lg font-semibold tracking-tight text-ink">Pengingat manual</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted">
                            Staf bengkel membuat pengingat berdasarkan tanggal atau kilometer motor Anda. Tidak ada prediksi otomatis.
                        </p>
                    </article>
                </div>
            </section>

            <section id="cara-kerja" class="bg-canvas">
                <div class="mx-auto max-w-7xl px-5 py-20 sm:px-8">
                    <div class="max-w-2xl">
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-dark">Cara kerja</p>
                        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-ink sm:text-4xl">Tiga langkah, motor siap jalan.</h2>
                    </div>

                    <div class="mt-10 grid gap-5 md:grid-cols-3">
                        <article class="rounded-2xl border border-line bg-white p-6">
                            <span class="text-xs font-bold text-brand-dark">01</span>
                            <h3 class="mt-3 text-lg font-semibold tracking-tight text-ink">Daftar &amp; tambahkan motor</h3>
                            <p class="mt-2 text-sm leading-relaxed text-muted">Buat akun pelanggan, lalu simpan data motor Anda. Maksimal lima motor per akun.</p>
                        </article>
                        <article class="rounded-2xl border border-line bg-white p-6">
                            <span class="text-xs font-bold text-brand-dark">02</span>
                            <h3 class="mt-3 text-lg font-semibold tracking-tight text-ink">Booking servis</h3>
                            <p class="mt-2 text-sm leading-relaxed text-muted">Pilih layanan dan slot yang tersedia. Booking otomatis dikonfirmasi bila kapasitas masih ada.</p>
                        </article>
                        <article class="rounded-2xl border border-line bg-white p-6">
                            <span class="text-xs font-bold text-brand-dark">03</span>
                            <h3 class="mt-3 text-lg font-semibold tracking-tight text-ink">Pantau &amp; bayar</h3>
                            <p class="mt-2 text-sm leading-relaxed text-muted">Ikuti progres servis, lalu selesaikan pembayaran di kasir. Riwayat tersimpan untuk Anda.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="pengingat" class="mx-auto max-w-7xl px-5 py-20 sm:px-8">
                <div class="grid gap-10 rounded-2xl bg-ink px-6 py-10 text-white sm:px-10 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand">Pengingat servis</p>
                        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-white">Tidak perlu menebak kapan harus servis.</h2>
                        <p class="mt-4 max-w-xl text-sm leading-relaxed text-white/55">
                            Pengingat dibuat manual oleh staf berdasarkan tanggal atau kilometer. Saat jatuh tempo, notifikasi muncul di aplikasi.
                        </p>
                    </div>
                    <div class="flex justify-start lg:justify-end">
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-[10px] bg-brand px-6 py-3.5 text-sm font-bold text-ink transition hover:bg-brand-accent">
                            Buat akun pelanggan
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-line">
            <div class="mx-auto flex max-w-7xl flex-col gap-6 px-5 py-10 sm:px-8 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-2.5">
                    <x-application-logo class="h-8 w-8" />
                    <span class="text-sm font-black tracking-tight text-ink">MOTOCARE</span>
                </div>

                <p class="text-xs text-muted">© {{ date('Y') }} MotoCare. Semua hak dilindungi.</p>

                <div class="flex items-center gap-5 text-xs font-medium text-muted">
                    <a href="{{ route('login') }}" class="hover:text-ink">Masuk Pelanggan</a>
                    <a href="{{ route('login.staff') }}" class="hover:text-ink">Portal Staf</a>
                </div>
            </div>
        </footer>
    </body>
</html>
