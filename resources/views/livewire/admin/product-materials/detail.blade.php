<div>
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('product-materials.index') }}" wire:navigate class="btn btn-ghost btn-sm px-2">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </a>
            <div>
                <h1 class="text-2xl font-black tracking-tight text-base-content">{{ $product->name }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="badge badge-neutral badge-sm">{{ $product->division->name ?? 'Tanpa Divisi' }}</span>
                    <span class="text-xs opacity-50">•</span>
                    <span class="text-xs opacity-50">Kategori: {{ $product->category->name ?? '-' }}</span>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <livewire:admin.product-materials.modals.bulk-create :product_id="$product->id" />
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Product Card & Summary --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="card bg-base-100 border border-base-300 shadow-sm overflow-hidden">
                <div class="aspect-video bg-base-200 flex items-center justify-center border-b border-base-300">
                    @if($product->foto_product)
                        <img src="{{ asset('storage/'.$product->foto_product) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    @else
                        <x-heroicon-o-cube class="w-16 h-16 opacity-10" />
                    @endif
                </div>
                <div class="card-body p-6">
                    <h2 class="text-sm font-bold uppercase tracking-widest opacity-40 mb-4">Ringkasan Produk</h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="opacity-60">Harga Jual</span>
                            <span class="font-bold text-primary">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="opacity-60">Total Bahan Baku</span>
                            <span class="badge badge-neutral font-mono font-bold">{{ $product->materials->count() }} Item</span>
                        </div>
                        <div class="flex justify-between items-center text-sm pt-4 border-t border-base-200">
                            <span class="opacity-60 font-bold">Estimasi HPP</span>
                            <span class="font-black text-lg">Rp {{ number_format($product->cost_price ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recipe Table --}}
        <div class="lg:col-span-2">
            <div class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body p-0">
                    <div class="p-6 border-b border-base-200 flex items-center justify-between">
                        <h3 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-beaker class="w-5 h-5 text-primary" />
                            Daftar Bahan Baku (Recipe / BOM)
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead class="bg-base-200/50 text-[10px] uppercase tracking-widest">
                                <tr>
                                    <th class="py-4 pl-6">Bahan Baku</th>
                                    <th class="py-4">Kategori</th>
                                    <th class="py-4 text-center">Takaran (Qty)</th>
                                    <th class="py-4 text-center">Estimasi Biaya</th>
                                    <th class="py-4 pr-6 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-base-200">
                                @forelse($product->materials as $material)
                                    <tr class="hover:bg-base-200/30 transition-colors">
                                        <td class="py-4 pl-6">
                                            <div class="font-bold text-sm">{{ $material->name }}</div>
                                            <div class="text-[10px] opacity-40 uppercase font-mono mt-0.5">ID: {{ $material->id }}</div>
                                        </td>
                                        <td class="py-4">
                                            <span class="badge badge-ghost badge-sm text-[10px]">{{ $material->category->name ?? '-' }}</span>
                                        </td>
                                        <td class="py-4 text-center font-mono font-bold">
                                            {{ number_format($material->pivot->qty_used, 3, ',', '.') }}
                                            <span class="text-[10px] font-normal opacity-50 uppercase ml-1">{{ $material->unit->name ?? 'Unit' }}</span>
                                        </td>
                                        <td class="py-4 text-center">
                                            @php
                                                $cost = $material->price * $material->pivot->qty_used;
                                            @endphp
                                            <div class="text-xs font-semibold">Rp {{ number_format($cost, 0, ',', '.') }}</div>
                                        </td>
                                        <td class="py-4 pr-6 text-center">
                                            <button 
                                                wire:click="confirmDelete({{ $material->id }})" 
                                                class="btn btn-ghost btn-xs text-error">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-20 text-center">
                                            <div class="flex flex-col items-center gap-3 opacity-20">
                                                <x-heroicon-o-beaker class="w-12 h-12" />
                                                <span class="italic text-sm">Belum ada bahan baku yang terdaftar untuk produk ini.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    <livewire:admin.product-materials.modals.delete />
</div>
