<x-form.modal
    modalId="modal_edit_recipe"
    title="Edit Resep Produk"
    saveButtonText="Perbarui"
    saveButtonIcon="heroicon-o-pencil"
    saveButtonClass="btn btn-primary gap-2 btn-sm"
    saveAction="update"
    modalSize="modal-box max-w-2xl"
    :showButton="false">

    <div class="grid grid-cols-1 gap-4">
        <x-form.searchable-select
            label="Pilih Produk"
            name="product_id"
            icon="heroicon-o-cube"
            placeholder="Pilih Produk"
            wire:model="product_id"
            :options="$availableProducts"
            :required="true" />

        <x-form.searchable-select
            label="Pilih Inventory/Bahan"
            name="material_id"
            icon="heroicon-o-archive-box"
            placeholder="Pilih Bahan Baku"
            wire:model="material_id"
            :options="$availableMaterials"
            :required="true" />

        @php
            $selectedMaterial = $availableMaterials->firstWhere('id', $material_id);
        @endphp

        <x-form.input
            label="Masukkan Jumlah Bahan"
            name="qty_used"
            type="number"
            icon="heroicon-o-calculator"
            placeholder="Contoh: 1.5"
            wire:model="qty_used"
            step="0.001"
            min="0.001"
            :required="true"
            validatorMessage="Jumlah bahan wajib diisi"
            hint="Jumlah bahan ini adalah kebutuhan per 1 unit produk yang dibuat.">
            <span class="badge badge-neutral badge-sm">
                {{ $selectedMaterial->unit->name ?? 'Unit' }}
            </span>
        </x-form.input>
    </div>

</x-form.modal>
