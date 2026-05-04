<div>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            @php
                $activeFilterCount = collect([
                    $filterSort !== 'newest' ? $filterSort : '',
                ])->filter(fn($value) => $value !== '')->count();
            @endphp
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <div class="join w-full md:w-64">
                        <label class="input input-sm input-bordered join-item flex items-center gap-2 w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                                placeholder="Cari produk atau bahan..." />
                        </label>
                    </div>

                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @if ($activeFilterCount > 0)
                                <span class="badge badge-primary badge-sm">{{ $activeFilterCount }}</span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-64 p-4 bg-base-100 border border-base-300 mt-2">
                            <div class="space-y-3">
                                <x-form.select
                                    label="Urutan"
                                    name="filterSort"
                                    wire:model.live="filterSort"
                                    class="select-sm"
                                >
                                    <option value="newest">Terbaru</option>
                                    <option value="oldest">Terlama</option>
                                </x-form.select>

                                <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-auto flex justify-end gap-2">
                    <livewire:admin.product-materials.modals.bulk-create />
                    <livewire:admin.product-materials.modals.create />
                </div>
            </div>

            <livewire:admin.product-materials.modals.edit />
            <livewire:admin.product-materials.modals.delete />

            <x-partials.table :columns="[
        ['label' => 'No', 'class' => 'w-16'],
        ['label' => 'Nama Produk'],
        ['label' => 'Divisi & Kategori'],
        ['label' => 'Total Bahan'],
        ['label' => 'Aksi', 'class' => 'text-center w-20']
    ]" :data="$products" emptyMessage="Belum ada data resep produk.">
                @foreach ($products as $index => $product)
                    <tr wire:key="product-{{ $product->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="font-medium text-base-content/50">{{ $products->firstItem() + $index }}</td>
                        <td>
                            <div class="font-bold">{{ $product->name }}</div>
                            <div class="text-[10px] text-base-content/40 italic">
                                Barcode: {{ $product->barcode ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <div class="badge badge-neutral badge-xs">{{ $product->division->name ?? '-' }}</div>
                            <div class="text-[10px] text-base-content/40 mt-1">
                                Kategori: {{ $product->category->name ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <span class="badge badge-primary font-bold">{{ $product->materials_count }}</span>
                                <span class="text-xs text-base-content/60">Bahan</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="dropdown dropdown-left dropdown-end">
                                <label tabindex="0" class="btn btn-ghost btn-xs">
                                    <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
                                </label>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-40 border border-base-200">
                                    <li>
                                        <a href="{{ route('product-materials.detail', $product->id) }}" wire:navigate>
                                            <x-heroicon-o-eye class="w-4 h-4 text-primary" />
                                            Detail Resep
                                        </a>
                                    </li>
                                    <li>
                                        <button type="button" wire:click="deleteRecipe({{ $product->id }})">
                                            <x-heroicon-o-trash class="w-4 h-4 text-error" />
                                            Hapus Resep
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6">
                <x-partials.pagination :paginator="$products" :perPage="$perPage" />
            </div>
        </div>
    </div>
</div>