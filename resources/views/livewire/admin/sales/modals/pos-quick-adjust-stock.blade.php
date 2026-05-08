<div>
    <x-form.modal
        modalId="pos-quick-adjust-stock-modal"
        title="⚡ Quick Adjust — Stok Produk"
        saveAction="save"
        saveButtonText="Simpan Penyesuaian"
        saveButtonIcon="heroicon-o-check"
        :showButton="false"
        modalSize="modal-box w-11/12 max-w-2xl">

        <div class="flex flex-col gap-3">
            {{-- Info Banner --}}
            <div class="alert alert-warning alert-sm text-xs py-2">
                <x-heroicon-o-bolt class="w-4 h-4 shrink-0" />
                <span>Shortcut admin — sesuaikan stok langsung dari POS tanpa berpindah halaman.</span>
            </div>

            {{-- Pilih Produk --}}
            <fieldset>
                <legend class="fieldset-legend">Pilih Produk <span class="text-error">*</span></legend>
                <input type="text" wire:model.live.debounce.300ms="searchProduct"
                    placeholder="Cari produk..."
                    class="input input-bordered input-sm w-full mb-2" />

                <select wire:model="selected_product_id"
                    class="select select-bordered w-full @error('selected_product_id') select-error @enderror">
                    <option value="">— Pilih Produk —</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">
                            {{ $product->name }}
                            (Stok: {{ $product->stock->qty_available ?? 0 }})
                        </option>
                    @endforeach
                </select>
                @error('selected_product_id') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
            </fieldset>

            {{-- Tipe Penyesuaian --}}
            <fieldset>
                <legend class="fieldset-legend">Tipe Penyesuaian</legend>
                <div class="flex gap-3">
                    <label class="label cursor-pointer flex gap-3 bg-success/10 p-3 rounded-lg border border-success/20 w-full">
                        <input type="radio" wire:model="adjustment_type" value="add" class="radio radio-success radio-sm" />
                        <div class="flex flex-col">
                            <span class="label-text font-bold text-success text-sm">Tambah Stok</span>
                            <span class="text-[10px] opacity-60">Input manual / restok / +</span>
                        </div>
                    </label>
                    <label class="label cursor-pointer flex gap-3 bg-error/10 p-3 rounded-lg border border-error/20 w-full">
                        <input type="radio" wire:model="adjustment_type" value="subtract" class="radio radio-error radio-sm" />
                        <div class="flex flex-col">
                            <span class="label-text font-bold text-error text-sm">Kurangi Stok</span>
                            <span class="text-[10px] opacity-60">Rusak / Expired / Dibuang / -</span>
                        </div>
                    </label>
                </div>
            </fieldset>

            {{-- Jumlah --}}
            <x-form.input
                label="Jumlah"
                name="adjustment_qty"
                type="number"
                wireModel="adjustment_qty"
                placeholder="0"
                min="1"
                step="1"
                :required="true">
                <span class="text-sm font-medium opacity-50 pr-1">pcs</span>
            </x-form.input>

            {{-- Keterangan --}}
            <fieldset>
                <legend class="fieldset-legend">Keterangan / Alasan <span class="text-error">*</span></legend>
                <textarea wire:model="adjustment_note"
                    class="textarea textarea-bordered w-full h-20 @error('adjustment_note') textarea-error @enderror"
                    placeholder="Misal: Stok awal, Koreksi stock opname, Barang expired..."></textarea>
                @error('adjustment_note')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

    </x-form.modal>
</div>
