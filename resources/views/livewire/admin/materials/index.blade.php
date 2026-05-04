<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari bahan..." />
                        </label>
                    </div>
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @if($filterCategory || $filterStatus !== '')
                                <span class="badge badge-primary badge-sm">{{ (!!$filterCategory) + ($filterStatus !== '') }}</span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-64 p-4 bg-base-100 border border-base-300 mt-2 shadow-md">
                            <div class="space-y-3">
                                <x-form.select label="Kategori" name="filterCategory" wire:model.live="filterCategory" placeholder="Semua Kategori" class="select-sm">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </x-form.select>
                                <x-form.select label="Status" name="filterStatus" wire:model.live="filterStatus" placeholder="Semua Status" class="select-sm">
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </x-form.select>
                                <button wire:click="$set('filterCategory', ''); $set('filterStatus', '')" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <livewire:admin.materials.modals.create />
                    <a wire:navigate href="{{ route('materials.import') }}" class="btn btn-success btn-soft btn-sm gap-2">
                        <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                        Import Excel
                    </a>
                </div>
            </div>

            @php
                $columns = [
                    ['label' => 'No', 'class' => 'w-16'],
                    ['label' => 'Nama Material'],
                    ['label' => 'Kategori & Satuan'],
                    ['label' => 'Harga Modal'],
                    ['label' => 'Supplier'],
                    ['label' => 'Stok Saat Ini', 'class' => 'text-center'],
                    ['label' => 'Min. Stok'],
                    ['label' => 'Status'],
                    ['label' => 'Aksi', 'class' => 'text-center w-20'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$materials" emptyMessage="Belum ada data material.">
                @foreach ($materials as $index => $material)
                    <tr wire:key="material-{{ $material->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $materials->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $material->name }}</div>
                            <div class="text-xs text-base-content/40 italic">ID:
                                MAT-{{ str_pad($material->id, 4, '0', STR_PAD_LEFT) }}</div>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                <span class="badge badge-sm badge-soft badge-info">{{ $material->category->name ?? '-' }}</span>
                                <span class="badge badge-sm badge-soft badge-warning">{{ $material->unit->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="text-sm font-bold text-primary">
                                Rp {{ number_format($material->price, 0, ',', '.') }}
                                <span class="text-[10px] font-normal text-base-content/40">/ {{ $material->unit->name ?? 'unit' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="text-sm font-medium">{{ $material->supplier->name ?? '-' }}</div>
                        </td>
                        <td class="text-center">
                            @php
                                $currentStock = $material->stock->qty_available ?? 0;
                                $isLow = $currentStock <= $material->minimum_stock;
                            @endphp
                            <div @class([
                                'font-mono font-bold text-lg',
                                'text-error' => $isLow,
                                'text-success' => !$isLow
                            ])>
                                {{ number_format($currentStock, 2, ',', '.') }}
                            </div>
                        </td>
                        <td>
                            <span class="text-sm font-semibold text-base-content/60">{{ number_format($material->minimum_stock, 2, ',', '.') }}</span>
                        </td>
                        <td>
                            @if($material->status)
                                <span class="badge badge-soft badge-success badge-sm">Aktif</span>
                            @else
                                <span class="badge badge-soft badge-error badge-sm">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action :id="$material->id" />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$materials" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $materials->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $materials->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $materials->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    {{-- Modal Components --}}
    <livewire:admin.materials.modals.edit />
    <livewire:admin.materials.modals.delete />
</div>