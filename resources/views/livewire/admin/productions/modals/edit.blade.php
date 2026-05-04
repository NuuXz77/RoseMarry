<x-form.modal
    modalId="modal_edit_production"
    title="Edit Rencana Produksi"
    saveButtonText="Simpan Perubahan"
    saveButtonIcon="heroicon-o-pencil-square"
    saveButtonClass="btn btn-primary gap-2 btn-sm"
    saveAction="update"
    modalSize="modal-box max-w-3xl"
    :showButton="false">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input
            label="Tanggal Produksi"
            name="production_date"
            type="date"
            icon="heroicon-o-calendar-days"
            wireModel="production_date"
            :required="true"
            validatorMessage="Tanggal produksi wajib diisi" />

        <x-form.searchable-select
            label="Shift"
            name="shift_id"
            icon="heroicon-o-clock"
            placeholder="Pilih Shift"
            wire:model="shift_id"
            :options="$shifts"
            :required="true" />
    </div>

    <x-form.searchable-select
        label="Produk yang Dibuat"
        name="product_id"
        icon="heroicon-o-cube"
        placeholder="Pilih Produk"
        wire:model="product_id"
        :options="$products"
        :required="true" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.searchable-select
            label="Kelompok Pelaksana"
            name="student_group_id"
            icon="heroicon-o-users"
            placeholder="Pilih Kelompok"
            wire:model="student_group_id"
            :options="$groups"
            :required="true" />

        <x-form.input
            label="Jumlah Produksi (pcs)"
            name="qty_produced"
            type="number"
            icon="heroicon-o-hashtag"
            placeholder="0"
            wireModel="qty_produced"
            min="1"
            :required="true"
            validatorMessage="Jumlah produksi wajib diisi" />
    </div>

</x-form.modal>
