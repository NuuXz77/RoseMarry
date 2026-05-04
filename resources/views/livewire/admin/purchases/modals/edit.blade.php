<div>
    <x-form.modal modalId="edit-purchase-modal" title="Edit Purchase" saveAction="update" saveButtonText="Perbarui"
        saveButtonIcon="heroicon-o-pencil-square" modalSize="modal-box w-11/12 max-w-2xl" :showButton="false">

        {{-- Alpine JS managed items & supplier toggle --}}
        <div x-data="{
            localItems: @entangle('items'),
            isGuest: @entangle('is_guest'),
            selectedMaterialId: '',
            itemQty: 1,
            itemPrice: 0,
            materials: {{ $materials->toJson() }},
            
            init() {
                this.$watch('selectedMaterialId', value => {
                    const material = this.materials.find(m => m.id == value);
                    if (material) {
                        this.itemPrice = parseFloat(material.price || 0);
                    } else {
                        this.itemPrice = 0;
                    }
                });
            },

            get total() {
                return this.localItems.reduce((sum, item) => sum + parseFloat(item.subtotal || 0), 0);
            },

            addItem() {
                if (!this.selectedMaterialId) {
                    alert('Pilih bahan terlebih dahulu');
                    return;
                }
                if (this.itemPrice <= 0) {
                    alert('Harga satuan harus lebih dari 0');
                    return;
                }

                const material = this.materials.find(m => m.id == this.selectedMaterialId);
                
                // Check if already exists
                let found = false;
                this.localItems.forEach((item, index) => {
                    if (item.material_id == this.selectedMaterialId) {
                        this.localItems[index].qty = parseFloat(this.localItems[index].qty) + parseFloat(this.itemQty);
                        this.localItems[index].subtotal = this.localItems[index].qty * this.localItems[index].price;
                        found = true;
                    }
                });

                if (!found) {
                    this.localItems.push({
                        material_id: material.id,
                        name: material.name,
                        qty: parseFloat(this.itemQty),
                        price: parseFloat(this.itemPrice),
                        subtotal: parseFloat(this.itemQty) * parseFloat(this.itemPrice)
                    });
                }

                // Sync total amount to Livewire
                $wire.set('total_amount', this.total);

                // Reset form
                this.selectedMaterialId = '';
                this.itemQty = 1;
                this.itemPrice = 0;
            },

            removeItem(index) {
                this.localItems.splice(index, 1);
                $wire.set('total_amount', this.total);
            }
        }">
            <div class="flex items-center gap-4 mb-4 bg-base-200/30 p-4 rounded-3xl border border-base-300/50">
                <div class="form-control">
                    <label class="label cursor-pointer gap-3">
                        <span class="label-text font-bold text-xs uppercase opacity-60">Supplier Terdaftar</span>
                        <input type="radio" name="supplier_type_edit" class="radio radio-primary radio-sm"
                            @click="isGuest = false" :checked="!isGuest" />
                    </label>
                </div>
                <div class="form-control">
                    <label class="label cursor-pointer gap-3">
                        <span class="label-text font-bold text-xs uppercase opacity-60">Guest (Manual)</span>
                        <input type="radio" name="supplier_type_edit" class="radio radio-secondary radio-sm"
                            @click="isGuest = true" :checked="isGuest" />
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <template x-if="!isGuest">
                    <x-form.select label="Supplier" name="supplier_id" wireModel="supplier_id"
                        placeholder="Pilih Supplier" :required="true">
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </x-form.select>
                </template>
                <template x-if="isGuest">
                    <x-form.input label="Nama Guest Supplier" name="guest_supplier" wireModel="guest_supplier"
                        placeholder="Masukkan nama toko/supplier..." :required="true" />
                </template>

                <x-form.input label="Nomor Invoice" name="invoice_number" wireModel="invoice_number"
                    placeholder="Contoh: INV-PUR-0001" :required="true" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <x-form.input label="Tanggal Purchase" name="date" type="date" wireModel="date" :required="true" />

                <x-form.select label="Status" name="status" wireModel="status" placeholder="Pilih Status"
                    :required="true">
                    <option value="pending">Pending</option>
                    <option value="received">Received</option>
                    <option value="cancelled">Cancelled</option>
                </x-form.select>
            </div>
            {{-- Items Selection Section --}}
            <div class="mt-8 pt-6 border-t border-base-300">
                <h3 class="text-sm font-bold uppercase tracking-widest text-base-content/50 mb-4 flex items-center gap-2">
                    <x-heroicon-o-beaker class="w-4 h-4" />
                    Daftar Bahan / Item
                </h3>

                <div class="bg-base-200/30 p-5 rounded-3xl border border-base-300/50">
                    {{-- Input Form Row using Components --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        <div class="md:col-span-5">
                            <x-form.searchable-select 
                                label="Pilih Bahan" 
                                name="selectedMaterialId" 
                                x-model="selectedMaterialId"
                                placeholder="Cari Bahan Baku..."
                                :options="$materials->map(fn($m) => ['id' => $m->id, 'name' => $m->name . ' (' . ($m->unit->name ?? '-') . ')'])->toArray()"
                                optionValue="id"
                                optionLabel="name"
                                containerClass="mb-0" />
                        </div>
                        <div class="md:col-span-2">
                            <x-form.input label="Qty" name="itemQty" type="number" step="0.01" x-model="itemQty" containerClass="mb-0" />
                        </div>
                        <div class="md:col-span-3">
                            <x-form.input label="Harga Satuan" name="itemPrice" type="number" x-model="itemPrice" containerClass="mb-0" />
                        </div>
                        <div class="md:col-span-2">
                            <button type="button" @click="addItem()" class="btn btn-primary w-full rounded-2xl shadow-lg shadow-primary/20 h-[52px]">
                                <x-heroicon-o-plus class="w-5 h-5" />
                                <span class="md:hidden">Tambah</span>
                            </button>
                        </div>
                    </div>

                    {{-- Table Items --}}
                    <div class="overflow-x-auto mt-6">
                        <table class="table table-sm w-full bg-base-100 rounded-2xl overflow-hidden shadow-sm">
                            <thead class="bg-base-300/30 text-[10px] uppercase tracking-widest">
                                <tr>
                                    <th class="py-3 pl-4">Item</th>
                                    <th class="text-center py-3">Qty</th>
                                    <th class="text-right py-3">Harga</th>
                                    <th class="text-right py-3">Subtotal</th>
                                    <th class="w-10 py-3 pr-4"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-base-200">
                                <template x-for="(item, index) in localItems" :key="index">
                                    <tr class="hover:bg-primary/5 transition-colors group">
                                        <td class="pl-4 font-bold text-xs" x-text="item.name"></td>
                                        <td class="text-center font-mono text-xs" x-text="new Intl.NumberFormat('id-ID').format(item.qty)"></td>
                                        <td class="text-right font-mono text-xs opacity-60" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.price)"></td>
                                        <td class="text-right font-mono font-bold text-xs text-primary" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.subtotal)"></td>
                                        <td class="pr-4 text-center">
                                            <button type="button" @click="removeItem(index)" class="btn btn-ghost btn-xs text-error p-0">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="localItems.length === 0">
                                    <tr>
                                        <td colspan="5" class="text-center py-10 opacity-30 italic text-xs">Belum ada item ditambahkan</td>
                                    </tr>
                                </template>
                            </tbody>
                            <template x-if="localItems.length > 0">
                                <tfoot class="bg-base-200/50 border-t border-base-300">
                                    <tr>
                                        <td colspan="3" class="text-right py-4 pr-4">
                                            <span class="text-[10px] font-bold uppercase tracking-widest opacity-50">Total Keseluruhan</span>
                                        </td>
                                        <td class="text-right py-4">
                                            <span class="text-lg font-black text-primary" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(total)"></span>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </template>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <x-form.input label="Total Amount (Otomatis)" name="total_amount" type="number" wireModel="total_amount" readonly hint="Total otomatis dihitung secara instan oleh sistem." />
            </div>

        </div>

        <div class="mt-4">

            <fieldset class="mt-4">
                <legend class="fieldset-legend">Catatan <span class="font-normal text-base-content/40">(Opsional)</span>
                </legend>
                <textarea wire:model="notes"
                    class="textarea textarea-bordered w-full @error('notes') textarea-error @enderror" rows="3"
                    placeholder="Catatan tambahan pembelian..."></textarea>
                @error('notes') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
            </fieldset>

    </x-form.modal>
</div>