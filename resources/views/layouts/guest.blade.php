@props(['title' => 'Masuk'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }} — {{ config('app.name', 'MotoCare') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white font-sans text-ink antialiased overflow-x-hidden">
        <div class="min-h-screen lg:grid lg:grid-cols-[minmax(0,604px)_1fr]">
            <aside class="relative hidden overflow-hidden bg-ink lg:flex lg:flex-col lg:justify-between lg:p-10">
                <div class="pointer-events-none absolute -right-24 top-1/3 h-[420px] w-[420px] rounded-full border border-white/10"></div>
                <div class="pointer-events-none absolute -right-10 top-1/3 h-[260px] w-[260px] rounded-full border border-white/10"></div>
                <div class="pointer-events-none absolute -bottom-10 right-0 text-brand/10">
                    <x-icon name="motorcycle" class="h-80 w-80" />
                </div>

                <a href="{{ route('home') }}" class="relative flex items-center gap-2.5">
                    <x-application-logo class="h-10 w-10" />
                    <span class="text-lg font-black tracking-tight text-white">MOTOCARE</span>
                </a>

                <div class="relative">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand">Rawat kendaraan, rawat perjalanan</p>
                    <h2 class="mt-4 max-w-md text-[40px] font-extrabold leading-[1.08] tracking-tight text-white">
                        Mitra terpercaya untuk setiap perjalanan Anda.
                    </h2>
                    <p class="mt-5 max-w-sm text-sm leading-relaxed text-white/55">
                        Kelola perawatan motor dengan lebih mudah, cepat, dan transparan.
                    </p>
                </div>

                <p class="relative text-xs text-white/30">© {{ date('Y') }} MotoCare. Semua hak dilindungi.</p>
            </aside>

            <main class="flex min-h-screen flex-col px-6 py-6 sm:px-10 lg:px-16">
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 lg:hidden">
                        <x-application-logo class="h-9 w-9" />
                        <span class="text-base font-black tracking-tight text-ink">MOTOCARE</span>
                    </a>

                    <a href="{{ route('home') }}" class="ml-auto inline-flex items-center gap-1.5 text-sm text-muted transition hover:text-ink">
                        <x-icon name="arrow-left" class="h-4 w-4" />
                        Kembali ke beranda
                    </a>
                </div>

                <div class="flex flex-1 items-center justify-center py-10">
                    <div class="w-full max-w-[420px]">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
