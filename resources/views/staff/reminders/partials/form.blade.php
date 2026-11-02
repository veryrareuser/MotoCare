@php($reminder = $reminder ?? null)

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="motorcycle_id" value="Motor" />
        <x-select-input id="motorcycle_id" name="motorcycle_id" class="mt-2 block w-full" required>
            <option value="">Pilih motor pelanggan</option>
            @foreach ($motorcycles as $motorcycle)
                <option value="{{ $motorcycle->id }}" @selected(old('motorcycle_id', $reminder?->motorcycle_id) == $motorcycle->id)>
                    {{ $motorcycle->displayName() }} — {{ $motorcycle->license_plate }}
                    — {{ $motorcycle->customer?->name ?? 'Pelanggan' }}
                    ({{ number_format($motorcycle->odometer_current, 0, ',', '.') }} km)
                </option>
            @endforeach
        </x-select-input>
        <p class="mt-1.5 text-xs text-muted">Pengingat selalu terhubung ke satu motor pelanggan.</p>
        <x-input-error :messages="$errors->get('motorcycle_id')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="title" value="Judul pengingat" />
        <x-text-input
            id="title"
            name="title"
            type="text"
            class="mt-2 block w-full"
            :value="old('title', $reminder?->title)"
            placeholder="Contoh: Servis rutin 16.000 km"
            required
            autofocus
            autocomplete="off"
        />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="due_date" value="Tanggal jatuh tempo" />
        <x-text-input
            id="due_date"
            name="due_date"
            type="date"
            class="mt-2 block w-full"
            :value="old('due_date', $reminder?->due_date?->format('Y-m-d'))"
        />
        <p class="mt-1.5 text-xs text-muted">Isi tanggal, odometer, atau keduanya.</p>
        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="due_odometer" value="Odometer jatuh tempo (km)" />
        <x-text-input
            id="due_odometer"
            name="due_odometer"
            type="number"
            min="0"
            max="9999999"
            step="1"
            class="mt-2 block w-full"
            :value="old('due_odometer', $reminder?->due_odometer)"
            placeholder="Contoh: 16000"
        />
        <p class="mt-1.5 text-xs text-muted">Tidak boleh lebih kecil dari odometer motor saat ini.</p>
        <x-input-error :messages="$errors->get('due_odometer')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="description" value="Deskripsi (opsional)" />
        <textarea
            id="description"
            name="description"
            rows="3"
            class="mt-2 block w-full rounded-[10px] border-line bg-white px-4 py-3 text-sm text-ink shadow-sm placeholder:text-muted/60 focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/25"
            placeholder="Catatan tambahan untuk pelanggan, misalnya pekerjaan yang disarankan."
        >{{ old('description', $reminder?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>
</div>
