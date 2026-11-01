@php($motorcycle = $motorcycle ?? null)

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <x-input-label for="brand" value="Merek" />
        <x-text-input
            id="brand"
            name="brand"
            type="text"
            class="mt-2 block w-full"
            :value="old('brand', $motorcycle?->brand)"
            placeholder="Contoh: Honda"
            required
            autofocus
            autocomplete="off"
        />
        <x-input-error :messages="$errors->get('brand')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="model" value="Model" />
        <x-text-input
            id="model"
            name="model"
            type="text"
            class="mt-2 block w-full"
            :value="old('model', $motorcycle?->model)"
            placeholder="Contoh: Vario 160"
            required
            autocomplete="off"
        />
        <x-input-error :messages="$errors->get('model')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="year" value="Tahun" />
        <x-text-input
            id="year"
            name="year"
            type="number"
            min="1900"
            max="{{ now()->year + 1 }}"
            class="mt-2 block w-full"
            :value="old('year', $motorcycle?->year)"
            placeholder="{{ now()->year }}"
            autocomplete="off"
        />
        <x-input-error :messages="$errors->get('year')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="license_plate" value="Nomor polisi" />
        <x-text-input
            id="license_plate"
            name="license_plate"
            type="text"
            class="mt-2 block w-full uppercase"
            :value="old('license_plate', $motorcycle?->license_plate)"
            placeholder="B 1234 XYZ"
            autocomplete="off"
        />
        <x-input-error :messages="$errors->get('license_plate')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="odometer_current" value="Odometer saat ini (km)" />
        <x-text-input
            id="odometer_current"
            name="odometer_current"
            type="number"
            min="0"
            step="1"
            class="mt-2 block w-full"
            :value="old('odometer_current', $motorcycle?->odometer_current ?? 0)"
            required
            autocomplete="off"
        />
        <p class="mt-1.5 text-xs text-muted">Isi angka pada odometer motor saat ini.</p>
        <x-input-error :messages="$errors->get('odometer_current')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Catatan (opsional)" />
        <textarea
            id="notes"
            name="notes"
            rows="3"
            class="mt-2 block w-full rounded-[10px] border-line bg-white px-4 py-3 text-sm text-ink shadow-sm placeholder:text-muted/60 focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/25"
            placeholder="Contoh: servis rutin setiap 4.000 km"
        >{{ old('notes', $motorcycle?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>
</div>
