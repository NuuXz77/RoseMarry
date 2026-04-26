@props([
    'modalId' => 'modal_default',
    'title' => 'Modal Title',
    'buttonText' => 'Tambah Data',
    'buttonIcon' => 'heroicon-o-plus',
    'buttonClass' => 'btn btn-primary btn-sm gap-2',
    'buttonHiddenText' => true, // Hide text on mobile
    'buttonBadge' => null,
    'buttonBadgeClass' => 'badge badge-secondary badge-sm absolute -top-1 -right-1',
    'saveButtonText' => 'Simpan',
    'saveButtonIcon' => 'heroicon-o-check',
    'saveButtonClass' => 'btn btn-primary gap-2 btn-sm',
    'saveAction' => 'save',
    'showButton' => true, // Toggle untuk trigger button
    'showSaveButton' => true, // Toggle untuk save button
    'modalSize' => 'modal-box', // modal-box, modal-box w-11/12 max-w-5xl, dll
])

<div
    x-data="modal('{{ $modalId }}')"
    x-on:open-modal.window="if ($event.detail?.id === '{{ $modalId }}') openModal()"
>
    @if($showButton)
        <button class="{{ $buttonClass }}" @click="openModal()">
            @if($buttonIcon)
                <x-dynamic-component :component="$buttonIcon" class="w-5 h-5" />
            @endif
            <span class="{{ $buttonHiddenText ? 'hidden sm:inline' : '' }}">{{ $buttonText }}</span>
            @if($buttonBadge !== null && $buttonBadge !== '')
                <span class="{{ $buttonBadgeClass }}">{{ $buttonBadge }}</span>
            @endif
        </button>
    @endif

    @teleport('body')
        <dialog id="{{ $modalId }}" class="modal backdrop-blur-sm" wire:ignore.self>
            <div class="{{ $modalSize }} border border-base-300">
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                </form>
                
                <h3 class="text-lg font-bold mb-4">{{ $title }}</h3>

                <form wire:submit.prevent="{{ $saveAction }}">
                    <!-- Content Slot -->
                    <div class="py-4">
                        {{ $slot }}
                    </div>

                    <!-- Form Actions -->
                    @if($showSaveButton)
                        <div class="flex justify-end gap-3 mt-6">
                            {{-- <button type="button" class="btn btn-ghost btn-sm" @click="closeModal()">
                                Batal
                            </button> --}}
                            <button type="submit" class="{{ $saveButtonClass }}" 
                                wire:loading.attr="disabled"
                                wire:target="{{ $saveAction }}">
                                <!-- Normal State Icon -->
                                <span wire:loading.remove wire:target="{{ $saveAction }}">
                                    @if($saveButtonIcon)
                                        <x-dynamic-component :component="$saveButtonIcon" class="w-5 h-5" />
                                    @endif
                                </span>
                                
                                <!-- Loading State Spinner -->
                                <span wire:loading wire:target="{{ $saveAction }}" class="loading loading-spinner loading-sm"></span>
                                
                                <!-- Normal State Text -->
                                <span wire:loading.remove wire:target="{{ $saveAction }}">{{ $saveButtonText }}</span>
                                
                                <!-- Loading State Text -->
                                <span wire:loading wire:target="{{ $saveAction }}">Memproses...</span>
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </dialog>
    @endteleport
</div>

