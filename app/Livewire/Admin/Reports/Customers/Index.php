<?php

namespace App\Livewire\Admin\Reports\Customers;

use App\Models\Customers;
use App\Models\Sales;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Laporan Pelanggan')]
class Index extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $search = '';
    public $sortBy = 'total_spent';
    public $sortDir = 'desc';
    public $perPage = 15;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate', 'endDate', 'search', 'sortBy', 'sortDir', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->sortBy = 'total_spent';
        $this->sortDir = 'desc';
        $this->resetPage();
    }

    public function setSorting(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'desc';
        }
        $this->resetPage();
    }

    public function render()
    {
        // Customer analytics query
        $customersQuery = Customers::query()
            ->select([
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.email',
                'customers.created_at',
                DB::raw('COUNT(DISTINCT sales.id) as total_transactions'),
                DB::raw('COALESCE(SUM(CASE WHEN sales.status = "paid" THEN sales.total_amount ELSE 0 END), 0) as total_spent'),
                DB::raw('COALESCE(SUM(CASE WHEN sales.status = "paid" THEN 1 ELSE 0 END), 0) as paid_count'),
                DB::raw('MAX(sales.created_at) as last_purchase_at'),
            ])
            ->leftJoin('sales', function ($join) {
                $join->on('customers.id', '=', 'sales.customer_id')
                    ->whereBetween(DB::raw('DATE(sales.created_at)'), [$this->startDate, $this->endDate]);
            })
            ->where('customers.status', true)
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('customers.name', 'like', '%' . $this->search . '%')
                        ->orWhere('customers.phone', 'like', '%' . $this->search . '%')
                        ->orWhere('customers.email', 'like', '%' . $this->search . '%');
                });
            })
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.email', 'customers.created_at');

        // Sorting
        $allowedSorts = ['name', 'total_transactions', 'total_spent', 'last_purchase_at', 'paid_count'];
        $sortColumn = in_array($this->sortBy, $allowedSorts) ? $this->sortBy : 'total_spent';
        $sortDirection = $this->sortDir === 'asc' ? 'asc' : 'desc';

        if ($sortColumn === 'name') {
            $customersQuery->orderBy('customers.name', $sortDirection);
        } else {
            $customersQuery->orderBy($sortColumn, $sortDirection);
        }

        $customers = $customersQuery->paginate($this->perPage);

        // Summary stats
        $summaryQuery = Sales::query()
            ->where('status', 'paid')
            ->whereNotNull('customer_id')
            ->whereBetween(DB::raw('DATE(created_at)'), [$this->startDate, $this->endDate]);

        $summary = [
            'total_customers' => Customers::where('status', true)->count(),
            'active_customers' => (clone $summaryQuery)->distinct('customer_id')->count('customer_id'),
            'total_revenue_from_customers' => (clone $summaryQuery)->sum('total_amount'),
            'avg_per_customer' => 0,
        ];

        if ($summary['active_customers'] > 0) {
            $summary['avg_per_customer'] = round($summary['total_revenue_from_customers'] / $summary['active_customers']);
        }

        // Top 5 customers
        $topCustomers = Customers::query()
            ->select([
                'customers.id',
                'customers.name',
                DB::raw('COUNT(sales.id) as tx_count'),
                DB::raw('SUM(sales.total_amount) as revenue'),
            ])
            ->join('sales', 'customers.id', '=', 'sales.customer_id')
            ->where('sales.status', 'paid')
            ->whereBetween(DB::raw('DATE(sales.created_at)'), [$this->startDate, $this->endDate])
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // Top products bought by customers
        $topProductsByCustomers = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'paid')
            ->whereNotNull('sales.customer_id')
            ->whereBetween(DB::raw('DATE(sales.created_at)'), [$this->startDate, $this->endDate])
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.qty) as total_qty'),
                DB::raw('COUNT(DISTINCT sales.customer_id) as buyer_count'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return view('livewire.admin.reports.customers.index', [
            'customers' => $customers,
            'summary' => $summary,
            'topCustomers' => $topCustomers,
            'topProductsByCustomers' => $topProductsByCustomers,
        ]);
    }
}
