<div>
    <x-form.modal 
        modalId="modal_bulk_create_recipe" 
        title="Tambah Resep Massal (BOM)" 
        buttonText="Tambah Resep (Bulk)"
        buttonIcon="heroicon-o-squares-plus" 
        buttonClass="btn btn-sm btn-primary"
        saveAction="save" 
        saveButtonText="Simpan Semua Resep" 
        saveButtonIcon="heroicon-o-check"
        modalSize="modal-box w-11/12 max-w-3xl" 
        :showButton="true">

        <div class="space-y-6">
            {{-- Product Selection --}}
            <div class="bg-base-200/30 p-4 rounded-3xl border border-base-300/50">
                <x-form.searchable-select 
                    label="Pilih Produk" 
                    name="product_id" 
                    wire:model="product_id"
                    placeholder="Pilih produk yang akan dibuat resepnya..." 
                    :options="$availableProducts"
                    :required="true"
                    containerClass="mb-0" />
            </div>

            {{-- Material Input Row --}}
            <div class="bg-base-200/50 p-5 rounded-3xl border border-dashed border-base-300">
                <h3 class="text-xs font-bold uppercase tracking-widest text-base-content/40 mb-4 flex items-center gap-2">
                    <x-heroicon-o-beaker class="w-4 h-4" />
                    Tambah Bahan ke Daftar
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    <div class="md:col-span-6">
                        <x-form.searchable-select 
                            label="Bahan Baku" 
                            name="temp_material_id" 
                            wire:model="temp_material_id"
                            placeholder="Cari bahan..."
                            :options="$availableMaterials->map(fn($m) => ['id' => $m->id, 'name' => $m->name . ' (' . ($m->unit->name ?? '-') . ')'])->toArray()"
                            containerClass="mb-0" />
                    </div>
                    <div class="md:col-span-4">
                        <x-form.input 
                            label="Jumlah Pemakaian" 
                            name="temp_qty_used" 
                            type="number" 
                            step="0.001" 
                            wire:model="temp_qty_used" 
                            placeholder="0.000"
                            containerClass="mb-0" />
                    </div>
                    <div class="md:col-span-2">
                        <button type="button" wire:click="addItem" class="btn btn-primary btn-block rounded-2xl shadow-lg shadow-primary/20">
                            <x-heroicon-o-plus class="w-5 h-5" />
                        </button>
                    </div>
                </div>
                
                @if($errors->has('temp_material_id') || $errors->has('temp_qty_used'))
                    <div class="mt-2 px-1">
                        @error('temp_material_id') <p class="text-error text-xs">{{ $message }}</p> @enderror
                        @error('temp_qty_used') <p class="text-error text-xs">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>

            {{-- Temporary Items Table --}}
            <div class="overflow-hidden border border-base-300 rounded-3xl bg-base-100">
                <table class="table table-sm w-full">
                    <thead class="bg-base-200/50 text-[10px] uppercase tracking-widest">
                        <tr>
                            <th class="py-3 pl-5">Bahan Baku</th>
                            <th class="py-3 text-center">Jumlah</th>
                            <th class="py-3 text-center w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @forelse($items as $index => $item)
                            <tr class="hover:bg-base-200/30 transition-colors">
                                <td class="py-3 pl-5 font-bold text-sm text-primary">{{ $item['name'] }}</td>
                                <td class="py-3 text-center font-mono text-sm">
                                    {{ number_format($item['qty_used'], 3, ',', '.') }} 
                                    <span class="text-[10px] font-normal opacity-50 uppercase">{{ $item['unit'] }}</span>
                                </td>
                                <td class="py-3 text-center">
                                    <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-ghost btn-xs text-error">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-12 text-center opacity-30 italic text-xs">
                                    <div class="flex flex-col items-center gap-2">
                                        <x-heroicon-o-beaker class="w-8 h-8" />
                                        <span>Daftar bahan masih kosong. Tambahkan bahan di atas.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @error('items')
                <p class="text-error text-xs text-center font-bold">{{ $message }}</p>
            @enderror
        </div>

        <script>
            document.addEventListener('livewire:initialized', () => {
                @this.on('close-bulk-create-modal', () => {
                    const modal = document.getElementById('modal_bulk_create_recipe');
                    if (modal) modal.close();
                });
            });
        </script>
    </x-form.modal>
</div>
