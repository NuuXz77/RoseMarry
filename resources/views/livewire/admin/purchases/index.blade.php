<div>
    @php
        $canCreatePurchase = auth()->user()->can('purchases.create') || auth()->user()->can('purchases.manage');
        $canEditPurchase = auth()->user()->can('purchases.edit') || auth()->user()->can('purchases.manage');
        $canDeletePurchase = auth()->user()->can('purchases.delete') || auth()->user()->can('purchases.manage');
    @endphp

    <div class="card bg-base-100 border border-base-300" style="overflow: visible !important;">
        <div class="card-body" style="overflow: visible !important;">
            @php
                $activeFilterCount = collect([
                    $filterStatus,
                ])->filter(fn($value) => $value !== '')->count();
            @endphp
            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center mb-6">
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <label class="input input-sm">
                        <x-bi-search class="w-3" />
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari invoice atau supplier..." />
                    </label>

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
                                    label="Status"
                                    name="filterStatus"
                                    placeholder="Semua Status"
                                    wire:model.live="filterStatus"
                                    class="select-sm"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </x-form.select>

                                <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($canCreatePurchase)
                    <livewire:admin.purchases.modals.create />
                @endif
            </div>

            <x-partials.table
                :columns="[
                    ['label' => 'Invoice', 'field' => 'invoice_number', 'sortable' => true],
                    ['label' => 'Supplier'],
                    ['label' => 'Total', 'field' => 'total_amount', 'sortable' => true],
                    ['label' => 'Status', 'field' => 'status', 'class' => 'text-center'],
                    ['label' => 'Tanggal', 'field' => 'created_at', 'sortable' => true],
                    ['label' => 'Aksi', 'class' => 'text-center']
                ]"
                :data="$purchases"
                :sortField="$sortField"
                :sortDirection="$sortDirection"
                emptyMessage="Tidak ada pembelian"
                emptyIcon="heroicon-o-truck">

                @foreach ($purchases as $index => $purchase)
                    <tr wire:key="purchase-{{ $purchase->id }}" class="hover:bg-primary/5 transition-colors duration-150 group">
                        <td>
                            <span class="font-mono font-bold text-xs bg-base-200 px-2 py-1 rounded-md">{{ $purchase->invoice_number }}</span>
                        </td>
                        <td>
                            <div class="flex flex-col">
                                <span class="font-bold text-sm">{{ $purchase->supplier?->name ?? $purchase->guest_supplier ?? '-' }}</span>
                                <span class="text-[10px] opacity-40 italic">{{ $purchase->supplier ? 'Supplier Terdaftar' : 'Guest Supplier' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="font-mono font-black text-primary">Rp {{ number_format($purchase->total_amount ?? 0, 0, ',', '.') }}</span>
                        </td>
                        <td class="text-center">
                            @php
                                $statusClass = match($purchase->status) {
                                    'received' => 'badge-soft badge-success',
                                    'pending' => 'badge-soft badge-warning',
                                    'cancelled' => 'badge-soft badge-error',
                                    default => 'badge-soft badge-ghost'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }} badge-sm font-bold uppercase tracking-wider">
                                {{ $purchase->status ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold">{{ $purchase->created_at->format('d M Y') }}</span>
                                <span class="text-[10px] opacity-50">{{ $purchase->created_at->format('H:i') }} WIB</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="flex justify-center">
                                <x-partials.dropdown-action
                                    :id="$purchase->id"
                                    :showEdit="$canEditPurchase"
                                    :showDelete="$canDeletePurchase" />
                            </div>
                        </td>
                    </tr>
                @endforeach

            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <div class="flex flex-col gap-4">
                    <div class="text-sm text-gray-600 text-center sm:text-left">
                        Menampilkan <span class="font-semibold">{{ $purchases->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $purchases->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $purchases->total() }}</span> data
                    </div>

                    <x-partials.pagination :paginator="$purchases" :perPage="$perPage" />
                </div>
            </div>
        </div>
    </div>

    <livewire:admin.purchases.modals.edit />
    <livewire:admin.purchases.modals.delete />

</div>
