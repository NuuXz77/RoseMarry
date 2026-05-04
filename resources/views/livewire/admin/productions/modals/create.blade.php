<x-form.modal
    modalId="modal_create_production"
    title="Buat Rencana Produksi Baru"
    buttonText="Buat Produksi"
    buttonIcon="heroicon-o-plus"
    buttonClass="btn btn-sm btn-primary"
    :buttonHiddenText="false"
    saveButtonText="Buat Rencana"
    saveButtonIcon="heroicon-o-check"
    saveAction="save"
    modalSize="modal-box max-w-3xl">

    @if ($isProductionLocked)
        <div class="alert alert-info text-sm mb-4">
            <x-heroicon-o-lock-closed class="w-5 h-5" />
            <span>Shift dan Kelompok otomatis mengikuti jadwal produksi akun kamu untuk tanggal yang dipilih.</span>
        </div>
    @endif

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
            :disabled="$isProductionLocked"
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
            :disabled="$isProductionLocked"
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
