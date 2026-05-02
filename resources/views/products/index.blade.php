@extends('layouts.app')

@section('title', 'Products')
@section('header', 'Products Inventory')

@section('breadcrumbs')
    <span class="text-gray-300">/</span>
    <span class="text-[hsl(var(--text-main))] font-medium">Products</span>
@endsection

@section('content')
<div class="space-y-6">
    
    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between gap-4">
        <form method="GET" action="{{ route('products.index') }}" class="flex-1 flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or SKU..." class="premium-input max-w-sm">
            
            <select name="category_id" class="premium-input max-w-xs">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            
            <label class="flex items-center gap-2 text-sm text-[hsl(var(--text-muted))]">
                <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} class="rounded border-[hsl(var(--border))] text-[hsl(var(--primary))] focus:ring-[hsl(var(--primary))]">
                Low Stock Only
            </label>
            
            <button type="submit" class="btn-secondary">Filter</button>
            @if(request()->anyFilled(['search', 'category_id', 'low_stock']))
                <a href="{{ route('products.index') }}" class="btn-secondary !text-[hsl(var(--danger))]">Clear</a>
            @endif
        </form>

        <a href="{{ route('products.create') }}" class="btn-primary flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Product
        </a>
    </div>

    <!-- Products Table -->
    <div class="premium-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Product Details</th>
                        <th>Category</th>
                        <th>Price (Cost / Sell)</th>
                        <th>Stock Level</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[hsl(var(--border))]">
                    @forelse($products as $product)
                        @php
                            $rowClass = '';
                            if ($product->quantity <= 0) $rowClass = 'row-out-of-stock';
                            elseif ($product->quantity <= $product->minimum_stock_level) $rowClass = 'row-low-stock';
                            $pct = $product->minimum_stock_level > 0
                                ? min(100, round(($product->quantity / $product->minimum_stock_level) * 100))
                                : 100;
                            $barColor = $pct <= 33 ? 'bg-[hsl(var(--danger))]' : ($pct <= 100 ? 'bg-amber-400' : 'bg-[hsl(var(--success))]');
                            if ($product->quantity > $product->minimum_stock_level) $barColor = 'bg-[hsl(var(--success))]';
                        @endphp
                        <tr class="group cursor-pointer {{ $rowClass }}" data-product-id="{{ $product->id }}" onclick="window.location='{{ route('products.show', $product) }}'">
                            <td>
                                <div class="font-semibold text-[hsl(var(--text-main))]">{{ $product->name }}</div>
                                <div class="text-sm text-[hsl(var(--text-muted))]">SKU: {{ $product->sku }} &bull; Unit: {{ $product->unit }}</div>
                                <div class="text-xs text-[hsl(var(--text-muted))] mt-1">Added by: {{ $product->creator->name ?? 'System' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $product->category->name ?? 'Uncategorized' }}</span>
                            </td>
                            <td>
                                <div class="text-[hsl(var(--text-main))]">KES {{ number_format($product->cost_price, 2) }}</div>
                                <div class="text-sm text-[hsl(var(--text-muted))]">KES {{ number_format($product->selling_price, 2) }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg font-bold tabular-nums {{ $product->quantity <= 0 ? 'text-[hsl(var(--danger))]' : ($product->quantity <= $product->minimum_stock_level ? 'text-amber-500' : 'text-[hsl(var(--text-main))]') }}">
                                        {{ $product->quantity }}
                                    </span>
                                    <span class="badge badge-{{ $product->stockStatusClass() }}">
                                        {{ $product->stockStatus() }}
                                    </span>
                                </div>
                                <div class="stock-bar-track w-28">
                                    <div class="stock-bar-fill {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <div class="text-xs text-[hsl(var(--text-muted))] mt-1">Min: {{ $product->minimum_stock_level }}</div>
                            </td>
                            <td class="text-right" onclick="event.stopPropagation();">
                                <a href="{{ route('products.show', $product) }}" class="btn-primary !py-1.5 !px-3 !text-xs">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-[hsl(var(--text-muted))]">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                <p class="text-lg font-medium">No products found</p>
                                <p class="text-sm mt-1">Adjust your filters or add a new product.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="p-4 border-t border-[hsl(var(--border))] bg-gray-50/50">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
