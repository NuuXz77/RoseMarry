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
        },
        
        init() {
            // Set initial label if selectedId exists
            if (this.selectedId) {
                const opt = this.options.find(o => o['{{ $optionValue }}'] == this.selectedId);
                if (opt) this.selectedLabel = opt['{{ $optionLabel }}'];
            }

            this.$watch('selectedId', (val) => {
                if (!val) {
                    this.selectedLabel = '';
                } else {
                    const opt = this.options.find(o => o['{{ $optionValue }}'] == val);
                    if (opt) this.selectedLabel = opt['{{ $optionLabel }}'];
                }
            });
        }
    }" 
    @click.away="open = false"
    {{ $attributes->whereStartsWith(['x-model', 'wire:model'])->merge(['x-modelable' => 'selectedId']) }}>
    
    @if($label)
        <legend class="fieldset-legend">{{ $label }}</legend>
    @endif

    <div class="relative group">
        <div 
            @click="open = !open"
            class="input input-bordered w-full flex items-center justify-between cursor-pointer hover:border-primary/50 transition-all duration-200 {{ $icon ? 'pl-10' : 'pl-4' }} pr-10 @error($name) input-error @enderror"
            :class="open ? 'border-primary ring-2 ring-primary/10' : 'border-base-300'">
            
            @if($icon)
                <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none opacity-50">
                    <x-dynamic-component :component="$icon" class="w-4 h-4" />
                </div>
            @endif

            <span class="truncate text-sm" x-text="selectedLabel || '{{ $placeholder }}'" :class="!selectedLabel ? 'opacity-40' : 'font-medium'"></span>
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
                <div x-show="filteredOptions.length === 0" class="py-10 text-center flex flex-col items-center gap-2 opacity-30">
                    <x-heroicon-o-face-frown class="w-8 h-8" />
                    <span class="text-xs italic">Bahan tidak ditemukan...</span>
                </div>
            </div>
        </div>
    </div>

    @error($name)
        <p class="text-error text-xs mt-1">{{ $message }}</p>
    @enderror
</fieldset>
