<div>
    <x-form.modal
        modalId="pos-quick-create-product-modal"
        title="⚡ Quick Add — Produk Baru"
        saveAction="save"
        saveButtonText="Tambah Produk"
        saveButtonIcon="heroicon-o-check"
        :showButton="false"
        modalSize="modal-box w-11/12 max-w-2xl">

        <div class="flex flex-col gap-3">
            {{-- Info Banner --}}
            <div class="alert alert-info alert-sm text-xs py-2">
                <x-heroicon-o-bolt class="w-4 h-4 shrink-0" />
                <span>Shortcut admin — tambah produk langsung dari POS tanpa berpindah halaman.</span>
            </div>

            {{-- Nama Produk --}}
            <x-form.input
                label="Nama Produk"
                name="name"
                wireModel="name"
                placeholder="Contoh: Espresso, Nasi Goreng, Croissant..."
                :required="true" />

            {{-- Barcode --}}
            <fieldset>
                <legend class="fieldset-legend">Barcode <span class="font-normal text-base-content/40">(Opsional)</span></legend>
                <label class="input input-bordered flex items-center gap-2 w-full @error('barcode') input-error @enderror">
                    <x-heroicon-o-qr-code class="w-4 h-4 opacity-50" />
                    <input type="text" wire:model="barcode"
                        class="grow bg-transparent focus:outline-none"
                        placeholder="Scan atau ketik barcode..." />
                </label>
                @error('barcode') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
            </fieldset>

            {{-- Foto Produk --}}
            <fieldset>
                <legend class="fieldset-legend">Foto Produk <span class="font-normal text-base-content/40">(Opsional)</span></legend>
                <input type="file" wire:model="foto_product" accept="image/*"
                    class="file-input file-input-bordered w-full file-input-sm @error('foto_product') file-input-error @enderror" />
                @if($foto_product)
                    <div class="mt-2">
                        <img src="{{ $foto_product->temporaryUrl() }}" alt="Preview"
                            class="w-16 h-16 object-cover rounded-lg border border-primary/30" />
                    </div>
                @endif
                @error('foto_product') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
            </fieldset>

            {{-- Kategori & Divisi --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <x-form.select
                    label="Kategori"
                    name="category_id"
                    wireModel="category_id"
                    placeholder="Pilih Kategori"
                    :required="true">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-form.select>

                <x-form.select
                    label="Divisi Produksi"
                    name="division_id"
                    wireModel="division_id"
                    placeholder="Pilih Divisi"
                    :required="true">
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}">{{ $division->name }} ({{ ucfirst($division->type) }})</option>
                    @endforeach
                </x-form.select>
            </div>

            {{-- Harga --}}
            <fieldset>
                <legend class="fieldset-legend">Harga Jual (Rp)</legend>
                <label class="input input-bordered flex items-center gap-2 w-full @error('price') input-error @enderror">
                    <span class="text-xs font-bold text-base-content/40 shrink-0">Rp</span>
                    <input type="number" wire:model="price"
                        class="grow bg-transparent focus:outline-none"
                        placeholder="0" min="0" />
                </label>
                @error('price') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
            </fieldset>

            {{-- Status --}}
            <fieldset>
                <legend class="fieldset-legend">Status</legend>
                <x-form.checkbox
                    wireModel="status"
                    label="Produk Aktif"
                    color="checkbox-success"
                    size="checkbox-sm" />
                <p class="text-xs text-base-content/50 ml-1">Produk nonaktif tidak akan muncul di POS.</p>
            </fieldset>
        </div>

    </x-form.modal>
</div>
