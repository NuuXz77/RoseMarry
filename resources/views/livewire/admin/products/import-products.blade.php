<div>
    {{-- Upload Section (Livewire) --}}
    <div class="card bg-base-100 border border-base-300">
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">
                        <x-heroicon-o-document-arrow-up class="w-6 h-6" />
                        Upload File Excel
                    </h2>
                    <button wire:click="downloadTemplate" class="btn btn-outline btn-sm gap-2">
                        <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                        Download Template Excel
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="alert alert-soft alert-info">
                            <x-heroicon-o-information-circle class="w-5 h-5 shrink-0" />
                            <div class="text-sm">
                                <p class="font-semibold mb-2">Format File Excel:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li><strong>Kolom A (Nama Produk):</strong> nama produk (wajib)</li>
                                    <li><strong>Kolom B (Barcode):</strong> barcode unik (opsional)</li>
                                    <li><strong>Kolom C (Kategori):</strong> nama kategori dengan tipe product (wajib)</li>
                                    <li><strong>Kolom D (Divisi):</strong> nama divisi produksi (wajib)</li>
                                    <li><strong>Kolom E (Harga):</strong> angka harga jual (wajib)</li>
                                    <li><strong>Kolom F (Status):</strong> active atau inactive (opsional, default: active)</li>
                                    <li><strong>Kolom G (Gambar):</strong> nama file gambar yang di-upload (opsional, maks 2MB, format: jpg, png, webp)</li>
                                </ul>
                                <p class="mt-2 text-warning">
                                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline" />
                                    Baris pertama (header) akan diabaikan. Mulai data dari baris ke-2.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="divider lg:hidden">Atau Upload File Anda</div>

                        <div class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-base-300 rounded-lg bg-base-200/50 hover:bg-base-200 transition-colors min-h-30">
                            <x-heroicon-o-document-arrow-up class="w-10 h-10 text-gray-400 mb-2" />
                            @if ($file)
                                <div class="text-center w-full">
                                    <p class="text-sm font-semibold text-success mb-1">
                                        <x-heroicon-o-check-circle class="w-4 h-4 inline" />
                                        File dipilih: {{ $file->getClientOriginalName() }}
                                    </p>
                                    <p class="text-xs text-gray-500 mb-2">Ukuran: {{ round($file->getSize() / 1024, 2) }} KB</p>
                                </div>
                            @else
                                <p class="text-gray-600 font-semibold mb-1 text-center text-sm">Pilih file Excel (.xlsx, .xls)</p>
                                <p class="text-xs text-gray-500 mb-3 text-center">Maksimal ukuran file: 5MB</p>
                                <label for="file-upload-products" class="btn btn-primary btn-sm gap-2 cursor-pointer">
                                    <x-heroicon-o-folder-open class="w-4 h-4" />
                                    Pilih File Excel
                                </label>
                                <input id="file-upload-products" type="file" wire:model="file" accept=".xlsx,.xls" class="hidden" />
                            @endif
                            @error('file')
                                <p class="text-error text-sm mt-2 text-center">
                                    <x-heroicon-o-exclamation-circle class="w-4 h-4 inline" />
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-primary/30 rounded-lg bg-primary/5 hover:bg-primary/10 transition-colors min-h-30">
                            <x-heroicon-o-photo class="w-10 h-10 text-primary/50 mb-2" />
                            @if (count($imageFiles) > 0)
                                <div class="text-center w-full">
                                    <p class="text-sm font-semibold text-primary mb-1">
                                        <x-heroicon-o-check-circle class="w-4 h-4 inline" />
                                        {{ count($imageFiles) }} gambar di-upload
                                    </p>
                                    <div class="flex flex-wrap gap-1 justify-center mt-1 mb-2 max-h-16 overflow-y-auto">
                                        @foreach ($imageFiles as $img)
                                            <span class="badge badge-xs badge-ghost">{{ $img->getClientOriginalName() }}</span>
                                        @endforeach
                                    </div>
                                    <label for="image-upload-products" class="btn btn-primary btn-xs gap-1 cursor-pointer">
                                        <x-heroicon-o-arrow-path class="w-3 h-3" />
                                        Ganti Gambar
                                    </label>
                                    <input id="image-upload-products" type="file" wire:model="imageFiles" accept="image/jpeg,image/png,image/webp" multiple class="hidden" />
                                </div>
                            @else
                                <p class="text-primary/70 font-semibold mb-1 text-center text-sm">Upload Gambar Produk (opsional)</p>
                                <p class="text-xs text-gray-500 mb-3 text-center">Maks 2MB/file • jpg, png, webp • sesuaikan nama file di kolom G Excel</p>
                                <label for="image-upload-products" class="btn btn-outline btn-primary btn-sm gap-2 cursor-pointer">
                                    <x-heroicon-o-photo class="w-4 h-4" />
                                    Pilih Gambar
                                </label>
                                <input id="image-upload-products" type="file" wire:model="imageFiles" accept="image/jpeg,image/png,image/webp" multiple class="hidden" />
                            @endif
                            @error('imageFiles.*')
                                <p class="text-error text-sm mt-2 text-center">
                                    <x-heroicon-o-exclamation-circle class="w-4 h-4 inline" />
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div wire:loading wire:target="file" class="text-center py-4 mt-4 border-t border-base-200">
                    <span class="loading loading-spinner loading-md text-primary"></span>
                    <p class="text-sm text-gray-600 mt-2">Memproses file...</p>
                </div>
                <div wire:loading wire:target="imageFiles" class="text-center py-4 mt-4 border-t border-base-200">
                    <span class="loading loading-spinner loading-md text-primary"></span>
                    <p class="text-sm text-gray-600 mt-2">Mengupload gambar...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Preview Section (Alpine.js) --}}
    <div class="card bg-base-100 border border-base-300 mt-6"
         x-data="importPreview()"
         x-on:preview-data-loaded.window="loadPreview($event.detail)"
         x-on:images-updated.window="updateImages($event.detail)"
    >
        <div class="card-body">
            {{-- Header --}}
            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center mb-4">
                <div>
                    <h2 class="card-title text-lg">
                        <x-heroicon-o-table-cells class="w-6 h-6" />
                        Preview Data Import
                    </h2>
                    <template x-if="rows.length > 0">
                        <p class="text-sm text-gray-500">
                            Menampilkan: <span class="font-semibold text-primary" x-text="filteredRows.length"></span>
                            / Total: <span class="font-semibold" x-text="rows.length"></span> baris
                            <template x-if="validCount > 0">
                                <span>| Valid: <span class="font-semibold text-success" x-text="validCount"></span></span>
                            </template>
                            <template x-if="errorCount > 0">
                                <span>| Error: <span class="font-semibold text-error" x-text="errorCount"></span></span>
                            </template>
                        </p>
                    </template>
                    <template x-if="rows.length === 0">
                        <p class="text-sm text-gray-400">Pilih file untuk melihat data preview</p>
                    </template>
                </div>

                <template x-if="rows.length > 0">
                    <div class="flex gap-2">
                        <button @click="clearAll()" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                            Batal
                        </button>
                        <template x-if="errorCount === 0">
                            <button @click="doImport()" class="btn btn-success btn-sm gap-2" wire:loading.attr="disabled" wire:target="importDataFromAlpine">
                                <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                                Import <span x-text="rows.length"></span> Data
                            </button>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Error alert --}}
            <template x-if="errorCount > 0">
                <div class="alert alert-error mb-4">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                    <span>Terdapat <span x-text="errorCount"></span> baris dengan error. Perbaiki error sebelum import.</span>
                </div>
            </template>

            {{-- Filters --}}
            <template x-if="rows.length > 0">
                <div class="flex flex-col sm:flex-row gap-3 mb-4 p-3 bg-base-200 rounded-lg">
                    <template x-if="availableSheets.length > 1">
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 font-semibold mb-1 flex items-center gap-1">
                                <x-heroicon-o-table-cells class="w-3.5 h-3.5" />
                                Filter Sheet
                            </p>
                            <div class="flex flex-wrap gap-1">
                                <button @click="filterSheet = 'all'" class="btn btn-xs" :class="filterSheet === 'all' ? 'btn-primary' : 'btn-ghost bg-base-100'">
                                    Semua (<span x-text="rows.length"></span>)
                                </button>
                                <template x-for="sheet in availableSheets" :key="sheet">
                                    <button @click="filterSheet = sheet" class="btn btn-xs" :class="filterSheet === sheet ? 'btn-primary' : 'btn-ghost bg-base-100'">
                                        <span x-text="sheet"></span> (<span x-text="rows.filter(r => r.sheet_name === sheet).length"></span>)
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="availableCategories.length > 0">
                        <div class="min-w-45">
                            <p class="text-xs text-gray-500 font-semibold mb-1 flex items-center gap-1">
                                <x-heroicon-o-tag class="w-3.5 h-3.5" />
                                Filter Kategori
                            </p>
                            <select x-model="filterCategory" class="select select-sm select-bordered w-full bg-base-100">
                                <option value="all">Semua Kategori</option>
                                <template x-for="cat in availableCategories" :key="cat">
                                    <option :value="cat" x-text="cat"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <template x-if="availableDivisions.length > 0">
                        <div class="min-w-45">
                            <p class="text-xs text-gray-500 font-semibold mb-1 flex items-center gap-1">
                                <x-heroicon-o-building-office-2 class="w-3.5 h-3.5" />
                                Filter Divisi
                            </p>
                            <select x-model="filterDivision" class="select select-sm select-bordered w-full bg-base-100">
                                <option value="all">Semua Divisi</option>
                                <template x-for="div in availableDivisions" :key="div">
                                    <option :value="div" x-text="div"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <template x-if="filterSheet !== 'all' || filterCategory !== 'all' || filterDivision !== 'all'">
                        <div class="flex items-end">
                            <button @click="filterSheet = 'all'; filterCategory = 'all'; filterDivision = 'all'" class="btn btn-xs btn-ghost text-error gap-1">
                                <x-heroicon-o-x-circle class="w-3.5 h-3.5" />
                                Reset Filter
                            </button>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr>
                            <th class="w-8">#</th>
                            <th x-show="availableSheets.length > 1" class="w-28">Sheet</th>
                            <th>Nama Produk</th>
                            <th class="w-36">Barcode</th>
                            <th class="w-32">Kategori</th>
                            <th class="w-32">Divisi</th>
                            <th class="w-28 text-right">Harga</th>
                            <th class="w-24 text-center">Status</th>
                            <th class="w-36">Gambar</th>
                            <th class="w-16 text-center">Valid</th>
                            <th class="w-64">Keterangan</th>
                            <th class="w-16 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="filteredRows.length > 0">
                            <template x-for="(row, fIdx) in filteredRows" :key="row._origIndex">
                                <tr class="hover:bg-base-200" :class="row.has_error ? 'bg-error/10' : ''">
                                    <td class="text-xs text-gray-400" x-text="row.row_number"></td>
                                    <td x-show="availableSheets.length > 1">
                                        <span class="badge badge-ghost badge-xs" x-text="row.sheet_name"></span>
                                    </td>
                                    <td>
                                        <input type="text" x-model="rows[row._origIndex].name"
                                            @input.debounce.300ms="validateRow(row._origIndex)"
                                            class="input input-xs input-bordered w-full" placeholder="Nama Produk">
                                    </td>
                                    <td>
                                        <input type="text" x-model="rows[row._origIndex].barcode"
                                            @input.debounce.300ms="validateRow(row._origIndex)"
                                            class="input input-xs input-bordered w-full" placeholder="Barcode (opsional)">
                                    </td>
                                    <td>
                                        <input type="text" x-model="rows[row._origIndex].category_name"
                                            @input.debounce.300ms="validateRow(row._origIndex)"
                                            class="input input-xs input-bordered w-full" placeholder="Kategori">
                                    </td>
                                    <td>
                                        <input type="text" x-model="rows[row._origIndex].division_name"
                                            @input.debounce.300ms="validateRow(row._origIndex)"
                                            class="input input-xs input-bordered w-full" placeholder="Divisi">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                            x-model.number="rows[row._origIndex].price"
                                            @input.debounce.300ms="validateRow(row._origIndex)"
                                            class="input input-xs input-bordered w-full text-right" placeholder="0">
                                    </td>
                                    <td class="text-center">
                                        <select x-model="rows[row._origIndex].status"
                                            @change="validateRow(row._origIndex)"
                                            class="select select-xs select-bordered w-full max-w-24">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-1">
                                            <input type="text" x-model="rows[row._origIndex].image_name"
                                                @input.debounce.300ms="validateRow(row._origIndex)"
                                                class="input input-xs input-bordered w-full" placeholder="nama_file.jpg">
                                            <template x-if="row.image_name && row.image_name.trim() !== ''">
                                                <template x-if="uploadedImages.includes(row.image_name.trim().toLowerCase())">
                                                    <x-heroicon-o-check-circle class="w-4 h-4 text-success shrink-0" />
                                                </template>
                                            </template>
                                            <template x-if="row.image_name && row.image_name.trim() !== '' && !uploadedImages.includes(row.image_name.trim().toLowerCase())">
                                                <x-heroicon-o-exclamation-circle class="w-4 h-4 text-warning shrink-0" />
                                            </template>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <template x-if="row.has_error">
                                            <x-heroicon-o-x-circle class="w-5 h-5 text-error inline" />
                                        </template>
                                        <template x-if="!row.has_error">
                                            <x-heroicon-o-check-circle class="w-5 h-5 text-success inline" />
                                        </template>
                                    </td>
                                    <td>
                                        <template x-if="row.errors && row.errors.length > 0">
                                            <ul class="text-xs text-error space-y-1">
                                                <template x-for="err in row.errors" :key="err">
                                                    <li x-text="'• ' + err"></li>
                                                </template>
                                            </ul>
                                        </template>
                                        <template x-if="!row.errors || row.errors.length === 0">
                                            <span class="text-xs text-success font-semibold">✓ OK</span>
                                        </template>
                                    </td>
                                    <td class="text-center">
                                        <button @click="removeRow(row._origIndex)" class="btn btn-ghost btn-xs text-error hover:bg-error/10 tooltip" data-tip="Hapus baris">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </template>

                        <template x-if="rows.length > 0 && filteredRows.length === 0">
                            <tr>
                                <td :colspan="availableSheets.length > 1 ? 12 : 11" class="text-center py-10">
                                    <div class="flex flex-col items-center gap-2 text-gray-400">
                                        <x-heroicon-o-funnel class="w-10 h-10 opacity-30" />
                                        <p class="font-semibold">Tidak ada data untuk filter ini</p>
                                        <button @click="filterSheet = 'all'; filterCategory = 'all'; filterDivision = 'all'" class="btn btn-xs btn-ghost text-primary">Reset Filter</button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <template x-if="rows.length === 0">
                            <tr>
                                <td colspan="11" class="text-center py-12">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <x-heroicon-o-document-magnifying-glass class="w-16 h-16 opacity-30" />
                                        <p class="text-lg font-semibold">Belum Ada Data Preview</p>
                                        <p class="text-sm">Upload file Excel untuk melihat data produk</p>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div wire:loading wire:target="importDataFromAlpine" class="text-center py-4 mt-4">
                <span class="loading loading-spinner loading-md text-success"></span>
                <p class="text-sm text-gray-600 mt-2">Mengimport data ke database...</p>
            </div>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('importPreview', () => ({
        rows: [],
        filterSheet: 'all',
        filterCategory: 'all',
        filterDivision: 'all',
        categoryMap: @js($categoryMap),
        divisionMap: @js($divisionMap),
        existingNames: @js($existingNames),
        existingBarcodes: @js($existingBarcodes),
        uploadedImages: @js($uploadedImages),

        init() {
            // Load initial data if already present
            let initial = $wire.get('previewData');
            if (initial && initial.length > 0) {
                this.rows = JSON.parse(JSON.stringify(initial));
            }
        },

        loadPreview(detail) {
            this.rows = JSON.parse(JSON.stringify(detail.rows || []));
            if (detail.refs) {
                this.categoryMap = detail.refs.categoryMap || {};
                this.divisionMap = detail.refs.divisionMap || {};
                this.existingNames = detail.refs.existingNames || [];
                this.existingBarcodes = detail.refs.existingBarcodes || [];
                this.uploadedImages = detail.refs.uploadedImages || [];
            }
            this.filterSheet = 'all';
            this.filterCategory = 'all';
            this.filterDivision = 'all';
        },

        updateImages(detail) {
            this.uploadedImages = detail.uploadedImages || [];
            this.validateAllRows();
        },

        get filteredRows() {
            return this.rows
                .map((row, i) => ({ ...row, _origIndex: i }))
                .filter(row => {
                    if (this.filterSheet !== 'all' && row.sheet_name !== this.filterSheet) return false;
                    if (this.filterCategory !== 'all' && row.category_name !== this.filterCategory) return false;
                    if (this.filterDivision !== 'all' && row.division_name !== this.filterDivision) return false;
                    return true;
                });
        },

        get validCount() {
            return this.rows.filter(r => !r.has_error).length;
        },

        get errorCount() {
            return this.rows.filter(r => r.has_error).length;
        },

        get availableSheets() {
            return [...new Set(this.rows.map(r => r.sheet_name).filter(Boolean))];
        },

        get availableCategories() {
            return [...new Set(this.rows.map(r => r.category_name).filter(Boolean))].sort();
        },

        get availableDivisions() {
            return [...new Set(this.rows.map(r => r.division_name).filter(Boolean))].sort();
        },

        validateRow(index) {
            let row = this.rows[index];
            if (!row) return;

            let errors = [];
            let name = (row.name || '').trim();
            let barcode = (row.barcode || '').trim();
            let catName = (row.category_name || '').trim();
            let divName = (row.division_name || '').trim();
            let price = row.price;
            let imgName = (row.image_name || '').trim();

            if (!name) {
                errors.push('Nama produk wajib diisi');
            } else if (this.existingNames.includes(name.toLowerCase())) {
                errors.push('Nama produk sudah terdaftar');
            }

            if (barcode && this.existingBarcodes.includes(barcode.toLowerCase())) {
                errors.push('Barcode sudah terdaftar');
            }

            if (!catName) {
                errors.push('Kategori wajib diisi');
            } else if (!(catName in this.categoryMap)) {
                errors.push('Kategori produk tidak ditemukan');
            } else {
                this.rows[index].category_id = this.categoryMap[catName];
            }

            if (!divName) {
                errors.push('Divisi wajib diisi');
            } else if (!(divName in this.divisionMap)) {
                errors.push('Divisi tidak ditemukan');
            } else {
                this.rows[index].division_id = this.divisionMap[divName];
            }

            if (price === null || price === '' || isNaN(price) || parseFloat(price) < 0) {
                errors.push('Harga harus angka >= 0');
            }

            if (imgName) {
                let ext = imgName.split('.').pop().toLowerCase();
                if (!['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                    errors.push('Format gambar harus jpg, jpeg, png, atau webp');
                } else if (this.uploadedImages.length > 0 && !this.uploadedImages.includes(imgName.toLowerCase())) {
                    errors.push('File gambar "' + imgName + '" tidak ditemukan di upload');
                }
            }

            this.rows[index].errors = errors;
            this.rows[index].has_error = errors.length > 0;
        },

        validateAllRows() {
            this.rows.forEach((_, i) => this.validateRow(i));
        },

        removeRow(index) {
            let name = this.rows[index]?.name || 'Baris ' + (this.rows[index]?.row_number || index);
            this.rows.splice(index, 1);
            $wire.dispatch('show-toast', { type: 'info', message: 'Data "' + name + '" dihapus dari preview' });
        },

        doImport() {
            this.validateAllRows();
            if (this.errorCount > 0) {
                $wire.dispatch('show-toast', { type: 'error', message: 'Ada ' + this.errorCount + ' data error. Perbaiki sebelum import.' });
                return;
            }
            $wire.importDataFromAlpine(JSON.parse(JSON.stringify(this.rows)));
        },

        clearAll() {
            this.rows = [];
            this.filterSheet = 'all';
            this.filterCategory = 'all';
            this.filterDivision = 'all';
            $wire.clearPreview();
        }
    }));

    $wire.on('redirect-after-import', () => {
        setTimeout(() => {
            window.location.href = '{{ route('products.index') }}';
        }, 1500);
    });
</script>
@endscript
