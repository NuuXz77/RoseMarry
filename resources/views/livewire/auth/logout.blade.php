<li wire:ignore>
    <a href="javascript:void(0)" @click="$dispatch('open-modal', {id: 'logout_modal'})"
        class="text-error flex items-center gap-2">
        <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
        <span>Keluar</span>
    </a>

    <div class="hidden">
        <x-form.modal
            modalId="logout_modal"
            title="Konfirmasi Logout"
            :showButton="false"
            saveAction="logout"
            saveButtonText="Ya, Keluar"
            saveButtonClass="btn btn-error gap-2 btn-sm"
            modalSize="modal-box max-w-md"
        >
            <p>Apakah Anda yakin ingin keluar dari aplikasi Rosemary?</p>
        </x-form.modal>
    </div>
</li>