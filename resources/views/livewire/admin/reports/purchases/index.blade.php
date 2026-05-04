<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card bg-accent text-accent-content shadow-lg">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest opacity-70">Total Pengeluaran</p>
                    <h2 class="text-2xl font-black">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</h2>
                </div>
                <x-heroicon-o-banknotes class="w-10 h-10 opacity-20" />
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Total Transaksi</p>
                    <h2 class="text-2xl font-black">{{ $summary['total_count'] }} <span
                            class="text-sm font-medium">Nota</span></h2>
                </div>
                <div class="p-3 bg-primary/10 text-primary rounded-2xl">
                    <x-heroicon-o-clipboard-document-list class="w-7 h-7" />
                </div>
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Diterima</p>
                    <h2 class="text-2xl font-black text-success">{{ $summary['received_count'] }} <span
                            class="text-sm font-medium">Nota</span></h2>
                </div>
                <div class="p-3 bg-success/10 text-success rounded-2xl">
                    <x-heroicon-o-check-circle class="w-7 h-7" />
                </div>
            </div>
        </div>
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-5 flex flex-row items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-base-content/50 uppercase tracking-widest">Pending</p>
                    <h2 class="text-2xl font-black text-warning">{{ $summary['pending_count'] }} <span
                            class="text-sm font-medium">Nota</span></h2>
                </div>
                <div class="p-3 bg-warning/10 text-warning rounded-2xl">
                    <x-heroicon-o-clock class="w-7 h-7" />
                </div>
            </div>
        </div>
    </div>

    {{-- Chart & Top Materials --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Tren Pembelian Chart --}}
        <div class="lg:col-span-2 card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6">
                <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-accent" />
                    Tren Pengeluaran Harian
                </h3>
                <div class="relative min-h-[250px]"
                    x-data="{
                        chart: null,
                        init() {
                            let options = {
                                series: [{ name: 'Total Pengeluaran', data: [] }],
                                chart: {
                                    type: 'area',
                                    height: 250,
                                    toolbar: { show: false },
                                    fontFamily: 'inherit',
                                    zoom: { enabled: false }
                                },
                                colors: ['#facc15'], {{-- Yellow/Accent matching brand --}}
                                fill: {
                                    type: 'gradient',
                                    gradient: {
                                        shadeIntensity: 1,
                                        opacityFrom: 0.4,
                                        opacityTo: 0.05,
                                        stops: [0, 90, 100]
                                    }
                                },
                                dataLabels: { enabled: false },
                                stroke: { curve: 'smooth', width: 3 },
                                xaxis: {
                                    categories: [],
                                    labels: { style: { colors: '#9ca3af', fontFamily: 'inherit' } },
                                    axisBorder: { show: false },
                                    axisTicks: { show: false }
                                },
                                yaxis: {
                                    labels: {
                                        formatter: (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val),
                                        style: { colors: '#9ca3af', fontFamily: 'inherit' }
                                    }
                                },
                                grid: {
                                    borderColor: 'rgba(156, 163, 175, 0.1)',
                                    strokeDashArray: 4,
                                },
                                tooltip: {
                                    y: { formatter: (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val) }
                                }
                            };
                            this.chart = new window.ApexCharts(this.$refs.chart, options);
                            this.chart.render();
                            this.updateChart();

                            let observer = new MutationObserver(() => this.updateChart());
                            observer.observe(this.$refs.dataContainer, { childList: true, subtree: true, characterData: true });
                        },
                        updateChart() {
                            if(!this.chart) return;
                            let labels = JSON.parse(this.$refs.dataLabels.textContent.trim() || '[]');
                            let series = JSON.parse(this.$refs.dataSeries.textContent.trim() || '[]');
                            this.chart.updateSeries([{ data: series }]);
                            this.chart.updateOptions({ xaxis: { categories: labels } });
                        }
                    }"
                >
                    <div x-ref="dataContainer" class="hidden">
                        <span x-ref="dataLabels">{{ $dailyPurchases->map(fn($t) => $t->date instanceof \Carbon\Carbon ? $t->date->format('d/m') : date('d/m', strtotime($t->date)))->toJson() }}</span>
                        <span x-ref="dataSeries">{{ $dailyPurchases->map(fn($t) => (int)$t->total)->toJson() }}</span>
                    </div>
                    <div wire:ignore x-ref="chart"></div>
                    @if($dailyPurchases->isEmpty())
                        <div class="absolute inset-0 flex items-center justify-center bg-base-100/50 italic opacity-30 text-sm">Tidak ada data pembelian</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Materials Chart --}}
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-6">
                <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-beaker class="w-5 h-5 text-secondary" />
                    Material Terbanyak Dibeli
                </h3>
                <div class="relative min-h-[250px]"
                    x-data="{
                        chart: null,
                        init() {
                            let options = {
                                series: [{ name: 'Total Qty', data: [] }],
                                chart: {
                                    type: 'bar',
                                    height: 250,
                                    toolbar: { show: false },
                                    fontFamily: 'inherit'
                                },
                                plotOptions: {
                                    bar: {
                                        horizontal: true,
                                        borderRadius: 4,
                                        barHeight: '60%'
                                    }
                                },
                                colors: ['#db2777'], {{-- Pink/Secondary matching theme --}}
                                dataLabels: { enabled: true, style: { fontSize: '10px' } },
                                xaxis: {
                                    categories: [],
                                    labels: { show: false },
                                    axisBorder: { show: false },
                                    axisTicks: { show: false }
                                },
                                grid: { show: false },
                                tooltip: {
                                    y: { formatter: (val) => val + ' unit' }
                                }
                            };
                            this.chart = new window.ApexCharts(this.$refs.chart, options);
                            this.chart.render();
                            this.updateChart();

                            let observer = new MutationObserver(() => this.updateChart());
                            observer.observe(this.$refs.dataContainer, { childList: true, subtree: true, characterData: true });
                        },
                        updateChart() {
                            if(!this.chart) return;
                            let labels = JSON.parse(this.$refs.dataLabels.textContent.trim() || '[]');
                            let series = JSON.parse(this.$refs.dataSeries.textContent.trim() || '[]');
                            this.chart.updateSeries([{ data: series }]);
                            this.chart.updateOptions({ xaxis: { categories: labels } });
                        }
                    }"
                >
                    <div x-ref="dataContainer" class="hidden">
                        <span x-ref="dataLabels">{{ $topMaterials->map(fn($m) => $m->name)->toJson() }}</span>
                        <span x-ref="dataSeries">{{ $topMaterials->map(fn($m) => (float)$m->total_qty)->toJson() }}</span>
                    </div>
                    <div wire:ignore x-ref="chart"></div>
                    @if($topMaterials->isEmpty())
                        <div class="absolute inset-0 flex items-center justify-center bg-base-100/50 italic opacity-30 text-sm">Belum ada data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Filters + Table --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-6 divide-y divide-base-200">
            @php
                $activeFilterCount = collect([
                    $filterSupplier,
                    $filterStatus,
                ])->filter(fn($value) => $value !== '')->count();
            @endphp
            <div class="flex flex-col xl:flex-row xl:items-end gap-4 mb-4">
                <div class="grid grid-cols-2 md:grid-cols-2 gap-3 grow">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Sejak</span></label>
                        <input type="date" wire:model.live="startDate" class="input input-bordered input-sm" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs uppercase">Sampai</span></label>
                        <input type="date" wire:model.live="endDate" class="input input-bordered input-sm" />
                    </div>
                </div>
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                        <x-heroicon-o-funnel class="w-5 h-5" />
                        Filter
                        @if ($activeFilterCount > 0)
                            <span class="badge badge-primary badge-sm">{{ $activeFilterCount }}</span>
                        @endif
                    </label>
                    <div tabindex="0" class="dropdown-content z-10 card card-compact w-80 p-4 bg-base-100 border border-base-300 mt-2">
                        <div class="space-y-3">
                            <div class="form-control">
                                <label class="label"><span class="label-text font-bold text-xs uppercase">Supplier</span></label>
                                <select wire:model.live="filterSupplier" class="select select-bordered select-sm">
                                    <option value="">Semua Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text font-bold text-xs uppercase">Status</span></label>
                                <select wire:model.live="filterStatus" class="select select-bordered select-sm">
                                    <option value="">Semua</option>
                                    <option value="received">Diterima</option>
                                    <option value="pending">Pending</option>
                                    <option value="cancelled">Batal</option>
                                </select>
                            </div>
                            <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-4 pb-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari invoice / supplier..."
                    class="input input-bordered input-sm w-full sm:w-64" />
                <button wire:click="export" class="btn btn-sm btn-success text-white">
                    <x-heroicon-o-document-arrow-down class="w-4 h-4" /> Export Excel
                </button>
            </div>

            {{-- Table --}}
            <div class="pt-4">
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr
                                class="text-base-content/40 uppercase text-[10px] tracking-widest border-b border-base-200">
                                <th>No</th>
                                <th>Invoice</th>
                                <th>Tanggal</th>
                                <th>Supplier</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchases as $index => $purchase)
                                <tr class="hover:bg-base-200/50 transition-colors">
                                    <td class="opacity-40">{{ $purchases->firstItem() + $index }}</td>
                                    <td><span class="font-bold text-primary">{{ $purchase->invoice_number }}</span></td>
                                    <td class="text-xs">{{ $purchase->date->format('d M Y') }}</td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ $purchase->supplier->name ?? $purchase->guest_supplier ?? '-' }}</span>
                                            <span
                                                class="text-[10px] opacity-40">{{ $purchase->supplier->phone ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col gap-0.5">
                                            @foreach($purchase->items->take(2) as $item)
                                                <span class="text-[10px]">{{ $item->material->name ?? '-' }}
                                                    ({{ number_format($item->qty, 1) }})</span>
                                            @endforeach
                                            @if($purchase->items->count() > 2)
                                                <span class="text-[10px] opacity-40">+{{ $purchase->items->count() - 2 }}
                                                    lainnya</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($purchase->status === 'received')
                                            <span class="badge badge-success badge-sm font-bold">Diterima</span>
                                        @elseif($purchase->status === 'pending')
                                            <span class="badge badge-warning badge-sm font-bold">Pending</span>
                                        @else
                                            <span class="badge badge-error badge-sm font-bold">Batal</span>
                                        @endif
                                    </td>
                                    <td class="text-right font-black">Rp
                                        {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                                    <td class="text-right text-[10px] font-bold opacity-50">
                                        {{ $purchase->creator->username ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-16 opacity-30 italic">
                                        <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto mb-2" />
                                        Data pembelian tidak ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($purchases->isNotEmpty())
                            <tfoot class="bg-base-200/50">
                                <tr class="font-black">
                                    <td colspan="6" class="text-right uppercase tracking-widest text-xs">Total Halaman Ini
                                    </td>
                                    <td class="text-right text-accent">Rp
                                        {{ number_format($purchases->sum('total_amount'), 0, ',', '.') }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
                <div class="mt-6 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-base-content/50">
                        Menampilkan <b>{{ $purchases->firstItem() ?? 0 }}</b> - <b>{{ $purchases->lastItem() ?? 0 }}</b>
                        dari <b>{{ $purchases->total() }}</b> transaksi
                    </div>
                    {{ $purchases->links() }}
                </div>
            </div>
        </div>
    </div>
</div>