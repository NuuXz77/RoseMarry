<?php

namespace App\Livewire\Admin\Reports\Purchases;

use App\Models\Purchases;
use App\Models\Suppliers;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Laporan Pembelian')]

    public $startDate;
    public $endDate;
    public $search = '';
    public $filterSupplier = '';
    public $filterStatus = '';
    public $perPage = 10;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate', 'endDate', 'search', 'filterSupplier', 'filterStatus'])) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->filterSupplier = '';
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PurchasesExport($this->startDate, $this->endDate, $this->filterSupplier, $this->filterStatus, $this->search), 
            'laporan_pembelian_'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function render()
    {
        // Base query (tanpa filter status — untuk summary)
        $baseQuery = Purchases::query()
            ->with(['supplier', 'creator', 'items.material'])
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->when($this->filterSupplier, fn($q) => $q->where('supplier_id', $this->filterSupplier))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('invoice_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', '%' . $this->search . '%'))
                        ->orWhere('guest_supplier', 'like', '%' . $this->search . '%');
                });
            });

        // Summary
        $summary = [
            'total_amount' => (clone $baseQuery)->where('status', 'received')->sum('total_amount'),
            'total_count' => (clone $baseQuery)->count(),
            'received_count' => (clone $baseQuery)->where('status', 'received')->count(),
            'pending_count' => (clone $baseQuery)->where('status', 'pending')->count(),
            'cancelled_count' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];

        // Query dengan filter status untuk tabel
        $query = clone $baseQuery;
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }
        $purchases = $query->orderBy('date', 'desc')->paginate($this->perPage);

        // Tren pembelian harian
        $dailyPurchases = Purchases::select(
            'date',
            DB::raw('SUM(total_amount) as total'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->where('status', 'received')
            ->when($this->filterSupplier, fn($q) => $q->where('supplier_id', $this->filterSupplier))
            ->groupBy('date')->orderBy('date')->get();

        // Top material terbanyak dibeli
        $topMaterials = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('materials', 'purchase_items.material_id', '=', 'materials.id')
            ->where('purchases.status', 'received')
            ->whereBetween('purchases.date', [$this->startDate, $this->endDate])
            ->when($this->filterSupplier, fn($q) => $q->where('purchases.supplier_id', $this->filterSupplier))
            ->select('materials.name', DB::raw('SUM(purchase_items.qty) as total_qty'), DB::raw('SUM(purchase_items.subtotal) as total_cost'))
            ->groupBy('materials.id', 'materials.name')
            ->orderByDesc('total_cost')->limit(5)->get();

        return view('livewire.admin.reports.purchases.index', [
            'purchases' => $purchases,
            'summary' => $summary,
            'dailyPurchases' => $dailyPurchases,
            'topMaterials' => $topMaterials,
            'suppliers' => Suppliers::orderBy('name')->get(),
        ]);
    }
}
