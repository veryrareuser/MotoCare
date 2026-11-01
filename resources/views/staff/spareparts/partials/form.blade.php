@php($sparepart = $sparepart ?? null)

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <x-input-label for="sku" value="SKU" />
        <x-text-input
            id="sku"
            name="sku"
            type="text"
            class="mt-2 block w-full uppercase"
            :value="old('sku', $sparepart?->sku)"
            placeholder="OIL-MTC-10W40"
            required
            autofocus
            autocomplete="off"
        />
        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="unit" value="Satuan" />
        <x-text-input
            id="unit"
            name="unit"
            type="text"
            class="mt-2 block w-full"
            :value="old('unit', $sparepart?->unit)"
            placeholder="pcs, botol, liter"
            required
            autocomplete="off"
        />
        <x-input-error :messages="$errors->get('unit')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="name" value="Nama sparepart" />
        <x-text-input
            id="name"
            name="name"
            type="text"
            class="mt-2 block w-full"
            :value="old('name', $sparepart?->name)"
            placeholder="Contoh: Oli mesin 10W-40"
            required
            autocomplete="off"
        />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="unit_price" value="Harga jual (Rp)" />
        <x-text-input
            id="unit_price"
            name="unit_price"
            type="number"
            min="0"
            step="500"
            class="mt-2 block w-full"
            :value="old('unit_price', $sparepart?->unit_price)"
            placeholder="55000"
            required
            autocomplete="off"
        />
        <x-input-error :messages="$errors->get('unit_price')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="minimum_stock" value="Stok minimum" />
        <x-text-input
            id="minimum_stock"
            name="minimum_stock"
            type="number"
            min="0"
            step="1"
            class="mt-2 block w-full"
            :value="old('minimum_stock', $sparepart?->minimum_stock ?? 0)"
            required
            autocomplete="off"
        />
        <p class="mt-1.5 text-xs text-muted">Sparepart muncul di daftar stok menipis saat saldo mencapai angka ini.</p>
        <x-input-error :messages="$errors->get('minimum_stock')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="description" value="Deskripsi (opsional)" />
        <textarea
            id="description"
            name="description"
            rows="3"
            class="mt-2 block w-full rounded-[10px] border-line bg-white px-4 py-3 text-sm text-ink shadow-sm placeholder:text-muted/60 focus:border-brand-accent focus:ring-2 focus:ring-brand-accent/25"
            placeholder="Spesifikasi singkat atau catatan pemakaian"
        >{{ old('description', $sparepart?->description) }}</textarea>
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
                @checked(old('is_active', $sparepart?->is_active ?? true))
            >
            <span>
                <span class="block text-sm font-semibold text-ink">Tampilkan di katalog</span>
                <span class="mt-0.5 block text-xs leading-relaxed text-muted">
                    Sparepart nonaktif tidak muncul di katalog pelanggan, tetapi riwayat stoknya tetap tersimpan.
                </span>
            </span>
        </label>
        <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
    </div>
</div>
