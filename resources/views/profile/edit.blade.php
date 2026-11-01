<x-app-layout title="Profil">
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="rounded-2xl border border-line bg-white p-6 shadow-card sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-white p-6 shadow-card sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        @if (auth()->user()->role === \App\Enums\UserRole::Customer)
            <div class="rounded-2xl border border-line bg-white p-6 shadow-card sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
