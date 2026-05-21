@props([
    'label' => '',
    'name' => '',
    'placeholder' => 'Cari...',
    'icon' => null,
    'required' => false,
    'wireModel' => '',
    'options' => [],
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'containerClass' => '',
    'allowCreate' => false,
    'createLabel' => 'Gunakan "%s" sebagai data baru',
    'noResultsText' => 'Bahan tidak ditemukan...',
    'allowClear' => false,
])

<fieldset class="{{ $containerClass }}" 
    x-data="{ 
        open: false, 
        search: '', 
        selectedId: null,
        selectedLabel: '',
        options: {{ json_encode($options) }},
        
        get filteredOptions() {
            if (!this.search) return this.options;
            return this.options.filter(opt => 
                (opt['{{ $optionLabel }}'] || '').toLowerCase().includes(this.search.toLowerCase())
            );
        },
        
        selectOption(opt) {
            this.selectedId = opt['{{ $optionValue }}'];
            this.selectedLabel = opt['{{ $optionLabel }}'];
            this.search = '';
            this.open = false;
            this.$dispatch('change', this.selectedId);
        },

        createOption() {
            const val = this.search.trim();
            this.selectedId = '';
            this.selectedLabel = val;
            this.search = '';
            this.open = false;
            this.$dispatch('create-option', val);
        },

        clearSelection() {
            this.selectedId = '';
            this.selectedLabel = '';
            this.search = '';
            this.$dispatch('change', '');
            this.$dispatch('clear-option');
        },
        
        init() {
            // Set initial label if selectedId exists
            if (this.selectedId) {
                const opt = this.options.find(o => o['{{ $optionValue }}'] == this.selectedId);
                if (opt) this.selectedLabel = opt['{{ $optionLabel }}'];
            } else if (typeof guestName !== 'undefined' && guestName) {
                this.selectedLabel = guestName;
            }
            
            this.$watch('selectedId', (val) => {
                if (!val) {
                    if (typeof guestName !== 'undefined' && guestName) {
                        this.selectedLabel = guestName;
                    } else {
                        this.selectedLabel = '';
                    }
                } else {
                    const opt = this.options.find(o => o['{{ $optionValue }}'] == val);
                    if (opt) this.selectedLabel = opt['{{ $optionLabel }}'];
                }
            });

            if (typeof guestName !== 'undefined') {
                this.$watch('guestName', (val) => {
                    if (!this.selectedId && val) {
                        this.selectedLabel = val;
                    } else if (!this.selectedId && !val) {
                        this.selectedLabel = '';
                    }
                });
            }
        }
    }" 
    @click.away="open = false"
    {{ $attributes->whereStartsWith(['x-model', 'wire:model'])->merge(['x-modelable' => 'selectedId']) }}>
    
    @if($label)
        <legend class="fieldset-legend">{{ $label }}</legend>
    @endif

    <div class="relative group">
        <div 
            @click="open = !open; if(open) { $nextTick(() => $refs.searchInput?.focus()); }"
            class="input input-bordered w-full flex items-center justify-between cursor-pointer hover:border-primary/50 transition-all duration-200 {{ $icon ? 'pl-10' : 'pl-4' }} pr-16 @error($name) input-error @enderror"
            :class="open ? 'border-primary ring-2 ring-primary/10' : 'border-base-300'">
            
            @if($icon)
                <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none opacity-50">
                    <x-dynamic-component :component="$icon" class="w-4 h-4" />
                </div>
            @endif

            <span class="truncate text-sm" x-text="selectedLabel || '{{ $placeholder }}'" :class="!selectedLabel ? 'opacity-40' : 'font-medium'"></span>
            
            {{-- Clear & Chevron buttons --}}
            <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
                <button type="button" x-show="selectedLabel && {{ $allowClear ? 'true' : 'false' }}" @click.stop="clearSelection()" class="btn btn-ghost btn-xs btn-circle opacity-50 hover:opacity-100">
                    <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                </button>
                <x-heroicon-o-chevron-down class="w-4 h-4 opacity-40 transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </div>
        </div>

        {{-- Dropdown Panel --}}
        <div 
            x-show="open" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-1"
            class="absolute z-50 w-full mt-2 bg-base-100 border border-base-300 rounded-2xl shadow-2xl overflow-hidden"
            style="display: none;">
            
            <div class="p-3 border-b border-base-200 bg-base-200/20">
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 opacity-30" />
                    <input 
                        type="text" 
                        x-model="search" 
                        placeholder="Ketik untuk mencari..." 
                        class="input input-sm bg-base-100 border-base-300 w-full pl-9 focus:border-primary focus:ring-0 rounded-xl text-sm"
                        @keydown.escape="open = false"
                        @keydown.enter.prevent="filteredOptions.length ? selectOption(filteredOptions[0]) : ({{ $allowCreate ? 'true' : 'false' }} && search.trim() ? createOption() : null)"
                        @click.stop
                        x-ref="searchInput">
                </div>
            </div>

            <div class="max-h-64 overflow-y-auto p-1.5 scrollbar-hide">
                <template x-for="opt in filteredOptions" :key="opt['{{ $optionValue }}']">
                    <div 
                        @click="selectOption(opt)"
                        class="px-4 py-2.5 text-sm hover:bg-primary hover:text-white rounded-xl cursor-pointer transition-all duration-150 flex items-center justify-between group"
                        :class="selectedId == opt['{{ $optionValue }}'] ? 'bg-primary/10 text-primary font-bold' : 'text-base-content/80'">
                        <span x-text="opt['{{ $optionLabel }}']"></span>
                        <x-heroicon-s-check x-show="selectedId == opt['{{ $optionValue }}']" class="w-4 h-4" />
                    </div>
                </template>
                
                {{-- Allow creation option --}}
                @if($allowCreate)
                    <template x-if="search.trim() && filteredOptions.filter(o => (o['{{ $optionLabel }}'] || '').toLowerCase() === search.trim().toLowerCase()).length === 0">
                        <div @click="createOption()"
                            class="px-4 py-2.5 text-sm hover:bg-success hover:text-white rounded-xl cursor-pointer transition-all duration-150 flex items-center gap-2 border-t border-base-200 mt-1 pt-2 font-semibold text-base-content/80">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            <span>{{ str_replace('%s', "' + search.trim() + '", $createLabel) }}</span>
                        </div>
                    </template>
                @endif

                <div x-show="filteredOptions.length === 0 && (!search.trim() || !{{ $allowCreate ? 'true' : 'false' }})" class="py-10 text-center flex flex-col items-center gap-2 opacity-30">
                    <x-heroicon-o-face-frown class="w-8 h-8" />
                    <span class="text-xs italic">{{ $noResultsText }}</span>
                </div>
            </div>
        </div>
    </div>

    @error($name)
        <p class="text-error text-xs mt-1">{{ $message }}</p>
    @enderror
</fieldset>

