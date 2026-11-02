@props(['title' => 'Dashboard'])

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
    <body class="min-h-screen bg-canvas font-sans text-ink antialiased overflow-x-hidden" x-data="{ sidebarOpen: false }">
        <div class="min-h-screen lg:pl-[248px]">
            @include('layouts.sidebar')

            <div class="flex min-h-screen flex-col">
                @include('layouts.topbar')

                <main class="flex-1 px-5 py-6 sm:px-8 lg:px-[34px] lg:py-8">
                    @if (session('success'))
                        <div class="mb-5 flex items-start gap-3 rounded-xl border border-brand-soft bg-brand-soft/60 px-4 py-3 text-sm font-medium text-brand-dark">
                            <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
