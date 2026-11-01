@php($serviceType = $serviceType ?? null)

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="Nama layanan" />
        <x-text-input
            id="name"
            name="name"
            type="text"
            class="mt-2 block w-full"
            :value="old('name', $serviceType?->name)"
            placeholder="Contoh: Servis ringan"
            required
            autofocus
            autocomplete="off"
        />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="base_price" value="Harga dasar (Rp)" />
        <x-text-input
            id="base_price"
            name="base_price"
            type="number"
            min="0"
            step="1000"
            class="mt-2 block w-full"
            :value="old('base_price', $serviceType?->base_price)"
            placeholder="75000"
            required
            autocomplete="off"
        />
        <x-input-error :messages="$errors->get('base_price')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="estimated_duration_minutes" value="Estimasi durasi (menit)" />
        <x-text-input
            id="estimated_duration_minutes"
            name="estimated_duration_minutes"
            type="number"
            min="1"
            max="1440"
            step="1"
            class="mt-2 block w-full"
            :value="old('estimated_duration_minutes', $serviceType?->estimated_duration_minutes)"
            placeholder="60"
            autocomplete="off"
        />
        <p class="mt-1.5 text-xs text-muted">Kosongkan bila durasi belum ditentukan.</p>
        <x-input-error :messages="$errors->get('estimated_duration_minutes')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="description" value="Deskripsi (opsional)" />
        <textarea
            id="description"
            name="description"
            rows="3"
            class="mt-2 block w-full rounded-[10px] border-line bg-white px-4 py-3 text-sm text-ink shadow-sm placeholder:text-muted/60 focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/25"
            placeholder="Jelaskan cakupan pekerjaan layanan ini"
        >{{ old('description', $serviceType?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <label for="is_active" class="flex cursor-pointer items-start gap-3 rounded-xl border border-line bg-canvas/50 px-4 py-3">
            <input
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                class="mt-0.5 h-4 w-4 rounded border-line text-brand-dark focus:ring-brand-accent"
                @checked(old('is_active', $serviceType?->is_active ?? true))
            >
            <span>
                <span class="block text-sm font-semibold text-ink">Tampilkan sebagai layanan aktif</span>
                <span class="mt-0.5 block text-xs leading-relaxed text-muted">
                    Layanan nonaktif tetap tersimpan untuk riwayat, tetapi tidak bisa dipilih saat booking.
                </span>
            </span>
        </label>
        <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
    </div>
</div>
