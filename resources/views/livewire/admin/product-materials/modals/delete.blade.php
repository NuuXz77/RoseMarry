<x-form.modal
    modalId="modal_delete_recipe"
    title="Hapus Resep Produk"
    saveButtonText="Ya, Hapus"
    saveButtonIcon="heroicon-o-trash"
    saveButtonClass="btn btn-error gap-2 btn-sm"
    saveAction="delete"
    modalSize="modal-box"
    :showButton="false">

    <div class="space-y-4">
        <div class="text-center">
            <x-heroicon-o-exclamation-triangle class="w-20 h-20 text-error mx-auto mb-4" />
            <h4 class="text-lg font-bold mb-2">Konfirmasi Penghapusan</h4>
            <p class="text-base-content/70 mb-1">
                {{ $isBulk ? 'Apakah Anda yakin ingin menghapus SELURUH resep untuk produk ini?' : 'Apakah Anda yakin ingin menghapus resep bahan ini?' }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <x-form.input
                label="Produk"
                name="productName"
                icon="heroicon-o-cube"
                wire:model="productName"
                :required="false"
                disabled />

            <x-form.input
                label="{{ $isBulk ? 'Informasi' : 'Bahan Baku' }}"
                name="materialName"
                icon="{{ $isBulk ? 'heroicon-o-information-circle' : 'heroicon-o-archive-box' }}"
                wire:model="materialName"
                :required="false"
                disabled />

            @if(!$isBulk)
                <x-form.input
                    label="Kebutuhan"
                    name="qty_used"
                    icon="heroicon-o-calculator"
                    wire:model="qty_used"
                    :required="false"
                    disabled>
                    <span class="badge badge-neutral badge-sm">{{ $unitName }}</span>
                </x-form.input>
            @endif
        </div>

        <p class="text-sm text-warning mt-4 text-center">
            <x-heroicon-o-information-circle class="w-4 h-4 inline" />
            Data yang dihapus tidak dapat dikembalikan.
        </p>
    </div>

</x-form.modal>
