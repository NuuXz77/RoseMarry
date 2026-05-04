@props([
    'columns' => [],
    'data' => [],
    'sortField' => null,
    'sortDirection' => 'asc',
    'emptyMessage' => 'Tidak ada data',
    'emptyIcon' => 'heroicon-o-inbox',
])

<div style="overflow-x: auto; overflow-y: visible !important;">
    <table class="table" style="position: relative;">
        <!-- Table Head -->
        <thead>
            <tr>
                @foreach($columns as $column)
                    @php
                        $alignmentClass = 'justify-start';
                        if (isset($column['class']) && str_contains($column['class'], 'text-center')) {
                            $alignmentClass = 'justify-center';
                        } elseif (isset($column['class']) && str_contains($column['class'], 'text-right')) {
                            $alignmentClass = 'justify-end';
                        }
                    @endphp
                    <th class="{{ $column['class'] ?? '' }}">
                        @if(isset($column['sortable']) && $column['sortable'])
                            <button wire:click="sortBy('{{ $column['field'] }}')" class="flex items-center {{ $alignmentClass }} gap-1 w-full hover:text-primary transition-colors">
                                {{ $column['label'] }}
                                @if($sortField === $column['field'])
                                    @if($sortDirection === 'asc')
                                        <x-heroicon-o-chevron-up class="w-4 h-4 text-primary" />
                                    @else
                                        <x-heroicon-o-chevron-down class="w-4 h-4 text-primary" />
                                    @endif
                                @else
                                    <x-heroicon-o-chevron-up-down class="w-4 h-4 opacity-30" />
                                @endif
                            </button>
                        @else
                            {{ $column['label'] }}
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>

        <!-- Table Body -->
        <tbody>
            @if($data->isEmpty())
                <tr>
                    <td colspan="{{ count($columns) }}" class="text-center py-8">
                        <div class="flex flex-col items-center gap-2 text-gray-500">
                            <x-dynamic-component :component="$emptyIcon" class="w-12 h-12 opacity-30" />
                            <p class="text-lg font-semibold">{{ $emptyMessage }}</p>
                            <p class="text-sm">Silakan tambah data baru</p>
                        </div>
                    </td>
                </tr>
            @else
                {{ $slot }}
            @endif
        </tbody>
    </table>
</div>