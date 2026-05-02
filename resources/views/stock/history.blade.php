@extends('layouts.app')

@section('title', 'Global Stock History')
@section('header', 'Stock Movement Audit Trail')

@section('content')
<div class="space-y-6">
    
    <!-- Filter Bar -->
    <div class="premium-card p-4">
        <form method="GET" action="{{ route('stock.history') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-xs text-[hsl(var(--text-muted))] mb-1 uppercase tracking-wider font-semibold">Filter by Product</label>
                <select name="product_id" class="premium-input text-sm py-2">
                    <option value="">All Products</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->sku }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-48">
                <label class="block text-xs text-[hsl(var(--text-muted))] mb-1 uppercase tracking-wider font-semibold">Movement Type</label>
                <select name="type" class="premium-input text-sm py-2">
                    <option value="">All Types</option>
                    <option value="IN" {{ request('type') === 'IN' ? 'selected' : '' }}>Stock In</option>
                    <option value="OUT" {{ request('type') === 'OUT' ? 'selected' : '' }}>Stock Out</option>
                    <option value="ADJUSTMENT" {{ request('type') === 'ADJUSTMENT' ? 'selected' : '' }}>Adjustment</option>
                </select>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-xs text-[hsl(var(--text-muted))] mb-1 uppercase tracking-wider font-semibold">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="premium-input text-sm py-2">
            </div>

            <div class="w-full md:w-48">
                <label class="block text-xs text-[hsl(var(--text-muted))] mb-1 uppercase tracking-wider font-semibold">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="premium-input text-sm py-2">
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary py-2 w-full md:w-auto">Filter</button>
                @if(request()->anyFilled(['product_id', 'type', 'date_from', 'date_to']))
                    <a href="{{ route('stock.history') }}" class="btn-secondary py-2 text-[hsl(var(--danger))]">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- History Table -->
    <div class="premium-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Product & SKU</th>
                        <th>Type / Source</th>
                        <th>Qty Change</th>
                        <th>Stock Bal.</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[hsl(var(--border))]">
                    @forelse($movements as $movement)
                        <tr class="hover:bg-gray-50/50">
                            <td>
                                <div class="font-medium text-[hsl(var(--text-main))]">{{ $movement->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-[hsl(var(--text-muted))]">{{ $movement->created_at->format('h:i:s A') }}</div>
                            </td>
                            <td>
                                <a href="{{ $movement->product ? route('products.show', $movement->product) : '#' }}" class="font-medium hover:text-[hsl(var(--primary))] transition-colors block">
                                    {{ $movement->product->name ?? 'Deleted Product' }}
                                </a>
                                <div class="text-xs text-[hsl(var(--text-muted))]">{{ $movement->product->sku ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="mb-1">
                                    <span class="badge badge-{{ $movement->typeBadgeClass() }}">{{ $movement->typeLabel() }}</span>
                                </div>
                                <div class="text-xs text-[hsl(var(--text-muted))]">
                                    {{ $movement->source ?? 'Manual' }} <br/> {{ $movement->reference }}
                                </div>
                            </td>
                            <td>
                                <span class="font-bold text-lg {{ $movement->type === 'OUT' ? 'text-[hsl(var(--danger))]' : 'text-[hsl(var(--success))]' }}">
                                    {{ $movement->signedQuantity() }}
                                </span>
                            </td>
                            <td>
                                <div class="font-mono text-sm bg-gray-100 px-2 py-1 rounded inline-block">
                                    <span class="text-[hsl(var(--text-muted))]">{{ $movement->quantity_before }}</span> → 
                                    <span class="font-bold text-[hsl(var(--text-main))]">{{ $movement->quantity_after }}</span>
                                </div>
                            </td>
                            <td class="text-sm">
                                {{ $movement->user->name ?? 'System' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-[hsl(var(--text-muted))]">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-lg font-medium">No movements found matching the filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-[hsl(var(--border))] bg-gray-50/50">
            {{ $movements->links() }}
        </div>
    </div>

</div>
@endsection
