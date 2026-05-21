<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card bg-primary text-primary-content shadow-lg">
            <div class="card-body p-4 flex flex-row items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest opacity-70">Total Pelanggan</p>
                    <h2 class="text-xl font-black">{{ number_format($summary['total_customers']) }}</h2>
                </div>
                <x-heroicon-o-user-group class="w-8 h-8 opacity-20" />
            </div>
        </div>
        <div class="card bg-success text-success-content shadow-lg">
            <div class="card-body p-4 flex flex-row items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest opacity-70">Pelanggan Aktif</p>
                    <h2 class="text-xl font-black">{{ number_format($summary['active_customers']) }}</h2>
                </div>
                <x-heroicon-o-check-badge class="w-8 h-8 opacity-20" />
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-4 flex flex-row items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-base-content/50 uppercase tracking-widest">Revenue Pelanggan</p>
                    <h2 class="text-xl font-black text-primary">Rp {{ number_format($summary['total_revenue_from_customers'], 0, ',', '.') }}</h2>
                </div>
                <x-heroicon-o-banknotes class="w-8 h-8 opacity-10" />
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-4 flex flex-row items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-base-content/50 uppercase tracking-widest">Rata-rata/Pelanggan</p>
                    <h2 class="text-xl font-black text-info">Rp {{ number_format($summary['avg_per_customer'], 0, ',', '.') }}</h2>
                </div>
                <x-heroicon-o-calculator class="w-8 h-8 opacity-10" />
            </div>
        </div>
    </div>

    {{-- Top Customers & Top Products --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top 5 Pelanggan --}}
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-5">
                <h3 class="font-bold text-sm flex items-center gap-2 mb-3">
                    <x-heroicon-o-trophy class="w-5 h-5 text-warning" />
                    Top 5 Pelanggan Terbanyak Belanja
                </h3>
                @if($topCustomers->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($topCustomers as $i => $tc)
                            @php
                                $maxRevenue = $topCustomers->max('revenue') ?: 1;
                                $pct = round(($tc->revenue / $maxRevenue) * 100);
                                $colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-error'];
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-black opacity-30 w-5 text-center">{{ $i + 1 }}</span>
                                <div class="grow min-w-0">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <span class="text-sm font-bold truncate">{{ $tc->name }}</span>
                                        <span class="text-xs font-bold text-primary shrink-0 ml-2">Rp {{ number_format($tc->revenue, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <progress class="progress {{ $colors[$i] ?? 'bg-primary' }} h-1.5 w-full" value="{{ $pct }}" max="100"></progress>
                                        <span class="text-[10px] text-base-content/40 shrink-0">{{ $tc->tx_count }}x</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center opacity-30">
                        <x-heroicon-o-user-group class="w-10 h-10 mx-auto mb-2" />
                        <p class="text-xs italic">Belum ada data pelanggan</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Produk Favorit Pelanggan --}}
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-5">
                <h3 class="font-bold text-sm flex items-center gap-2 mb-3">
                    <x-heroicon-o-fire class="w-5 h-5 text-orange-500" />
                    Produk Favorit Pelanggan
                </h3>
                @if($topProductsByCustomers->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($topProductsByCustomers as $i => $tp)
                            @php
                                $maxQty = $topProductsByCustomers->max('total_qty') ?: 1;
                                $pct = round(($tp->total_qty / $maxQty) * 100);
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-black opacity-30 w-5 text-center">{{ $i + 1 }}</span>
                                <div class="grow min-w-0">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <span class="text-sm font-bold truncate">{{ $tp->name }}</span>
                                        <span class="text-xs text-base-content/60 shrink-0 ml-2">{{ $tp->total_qty }} pcs · {{ $tp->buyer_count }} pembeli</span>
                                    </div>
                                    <progress class="progress progress-info h-1.5 w-full" value="{{ $pct }}" max="100"></progress>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center opacity-30">
                        <x-heroicon-o-shopping-bag class="w-10 h-10 mx-auto mb-2" />
                        <p class="text-xs italic">Belum ada data pembelian</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Customer Table --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-6 divide-y divide-base-200">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4 mb-4">
                <div class="grid grid-cols-2 gap-3 grow">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Sejak</span></label>
                        <input type="date" wire:model.live="startDate" class="input input-bordered input-sm" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Sampai</span></label>
                        <input type="date" wire:model.live="endDate" class="input input-bordered input-sm" />
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-4 pb-2">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama / telp / email..."
                    class="input input-bordered input-sm w-full sm:w-64" />
                <div class="flex items-center gap-2">
                    <select wire:model.live="perPage" class="select select-bordered select-sm">
                        <option value="15">15</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            {{-- Table --}}
            <div class="pt-4">
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr class="text-base-content/40 uppercase text-[10px] tracking-widest border-b border-base-200">
                                <th>No</th>
                                <th>
                                    <button wire:click="setSorting('name')" class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Pelanggan
                                        @if($sortBy === 'name')
                                            <x-heroicon-s-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                        @endif
                                    </button>
                                </th>
                                <th class="text-center">
                                    <button wire:click="setSorting('total_transactions')" class="flex items-center gap-1 hover:text-primary transition-colors mx-auto">
                                        Transaksi
                                        @if($sortBy === 'total_transactions')
                                            <x-heroicon-s-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                        @endif
                                    </button>
                                </th>
                                <th class="text-right">
                                    <button wire:click="setSorting('total_spent')" class="flex items-center gap-1 hover:text-primary transition-colors ml-auto">
                                        Total Belanja
                                        @if($sortBy === 'total_spent')
                                            <x-heroicon-s-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                        @endif
                                    </button>
                                </th>
                                <th class="text-center">
                                    <button wire:click="setSorting('last_purchase_at')" class="flex items-center gap-1 hover:text-primary transition-colors mx-auto">
                                        Terakhir Beli
                                        @if($sortBy === 'last_purchase_at')
                                            <x-heroicon-s-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                        @endif
                                    </button>
                                </th>
                                <th class="text-center">Tipe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $index => $cust)
                                <tr class="hover:bg-base-200/50 transition-colors">
                                    <td class="opacity-40">{{ $customers->firstItem() + $index }}</td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-bold">{{ $cust->name }}</span>
                                            @if($cust->phone)
                                                <span class="text-[10px] opacity-40">{{ $cust->phone }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-sm {{ $cust->total_transactions > 0 ? 'badge-primary badge-soft' : 'badge-ghost' }} font-bold">
                                            {{ $cust->total_transactions }}x
                                        </span>
                                    </td>
                                    <td class="text-right font-black {{ $cust->total_spent > 0 ? 'text-primary' : 'opacity-30' }}">
                                        Rp {{ number_format($cust->total_spent, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center text-xs">
                                        @if($cust->last_purchase_at)
                                            {{ \Carbon\Carbon::parse($cust->last_purchase_at)->format('d M Y') }}
                                        @else
                                            <span class="opacity-30">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($cust->paid_count >= 5)
                                            <span class="badge badge-warning badge-xs font-bold gap-1 px-2 py-2">
                                                ⭐ Loyal
                                            </span>
                                        @elseif($cust->paid_count >= 2)
                                            <span class="badge badge-success badge-xs font-bold gap-1 px-2 py-2">
                                                Repeat
                                            </span>
                                        @elseif($cust->paid_count >= 1)
                                            <span class="badge badge-info badge-xs font-bold gap-1 px-2 py-2">
                                                Baru
                                            </span>
                                        @else
                                            <span class="badge badge-ghost badge-xs font-bold px-2 py-2">
                                                Belum Beli
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-16 opacity-30 italic">
                                        <x-heroicon-o-user-group class="w-12 h-12 mx-auto mb-2" />
                                        Data pelanggan tidak ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-base-content/50">
                        Menampilkan <b>{{ $customers->firstItem() ?? 0 }}</b> - <b>{{ $customers->lastItem() ?? 0 }}</b> dari
                        <b>{{ $customers->total() }}</b> pelanggan
                    </div>
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
