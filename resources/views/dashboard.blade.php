@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Overview')

@section('content')
<div class="space-y-6">

    <!-- KPI Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

        <div class="stat-card group flex-col !items-stretch gap-3">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-[hsl(var(--text-muted))] uppercase tracking-widest mb-2">Total Products</p>
                    <h3 class="text-4xl font-black text-[hsl(var(--text-main))] tabular-nums">{{ $totalProducts }}</h3>
                    <p class="text-xs text-[hsl(var(--text-muted))] mt-2">Active SKUs in system</p>
                </div>
                <div class="stat-card-icon bg-blue-50 text-[hsl(var(--primary))]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
            </div>
            <canvas id="sparkline-in" height="40"></canvas>
        </div>

        <div class="stat-card group flex-col !items-stretch gap-3">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-[hsl(var(--text-muted))] uppercase tracking-widest mb-2">Categories</p>
                    <h3 class="text-4xl font-black text-[hsl(var(--text-main))] tabular-nums">{{ $totalCategories }}</h3>
                    <p class="text-xs text-[hsl(var(--text-muted))] mt-2">Product classifications</p>
                </div>
                <div class="stat-card-icon bg-indigo-50 text-indigo-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                </div>
            </div>
            <canvas id="sparkline-out" height="40"></canvas>
        </div>

        <div class="stat-card group {{ $lowStockProducts->count() > 0 ? 'border-l-4 border-l-amber-400' : '' }}">
            <div>
                <p class="text-xs font-bold text-[hsl(var(--text-muted))] uppercase tracking-widest mb-2">Low Stock</p>
                <h3 class="text-4xl font-black tabular-nums {{ $lowStockProducts->count() > 0 ? 'text-amber-500' : 'text-[hsl(var(--text-main))]' }}">{{ $lowStockProducts->count() }}</h3>
                <p class="text-xs text-[hsl(var(--text-muted))] mt-2">Items needing reorder</p>
            </div>
            <div class="stat-card-icon {{ $lowStockProducts->count() > 0 ? 'bg-amber-50 text-amber-500' : 'bg-gray-50 text-gray-400' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
        </div>

        <div class="stat-card group {{ $outOfStockCount > 0 ? 'border-l-4 border-l-red-400' : '' }}">
            <div>
                <p class="text-xs font-bold text-[hsl(var(--text-muted))] uppercase tracking-widest mb-2">Out of Stock</p>
                <h3 class="text-4xl font-black tabular-nums {{ $outOfStockCount > 0 ? 'text-[hsl(var(--danger))]' : 'text-[hsl(var(--text-main))]' }}">{{ $outOfStockCount }}</h3>
                <p class="text-xs text-[hsl(var(--text-muted))] mt-2">Needs immediate action</p>
            </div>
            <div class="stat-card-icon {{ $outOfStockCount > 0 ? 'bg-red-50 text-[hsl(var(--danger))]' : 'bg-gray-50 text-gray-400' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = @json($sparkLabels);
        const sparkIn  = @json($sparkIn);
        const sparkOut = @json($sparkOut);
        const sparkOpts = (data, color) => ({
            type: 'line',
            data: { labels, datasets: [{ data, borderColor: color, borderWidth: 2, pointRadius: 0, fill: true, backgroundColor: color + '20', tension: 0.4 }] },
            options: { responsive: true, plugins: { legend: { display: false }, tooltip: { enabled: false } }, scales: { x: { display: false }, y: { display: false, beginAtZero: true } }, animation: { duration: 800 } }
        });
        new Chart(document.getElementById('sparkline-in'),  sparkOpts(sparkIn,  '#22c55e'));
        new Chart(document.getElementById('sparkline-out'), sparkOpts(sparkOut, '#ef4444'));
    });
    </script>


    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Recent Stock Activity — Timeline Feed -->
        <div class="lg:col-span-2 premium-card overflow-hidden flex flex-col">
            <div class="section-header">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-[hsl(var(--primary))] rounded-full animate-pulse"></div>
                    <span class="section-title">Live Activity Feed</span>
                </div>
                <a href="{{ route('stock.history') }}" class="text-xs font-semibold text-[hsl(var(--primary))] hover:underline flex items-center gap-1">
                    View All
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>

            <div class="p-6">
                @forelse($recentMovements as $movement)
                    <div class="timeline-item">
                        <!-- Timeline dot color by type -->
                        <div class="timeline-dot
                            @if($movement->type === 'IN') bg-[hsl(var(--success-light))] text-[hsl(var(--success))]
                            @elseif($movement->type === 'OUT') bg-[hsl(var(--danger-light))] text-[hsl(var(--danger))]
                            @else bg-[hsl(var(--warning-light))] text-amber-600
                            @endif">
                            @if($movement->type === 'IN')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            @elseif($movement->type === 'OUT')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0 pt-1">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-[hsl(var(--text-main))] truncate">
                                        {{ $movement->product->name ?? 'Deleted Product' }}
                                    </p>
                                    <p class="text-xs text-[hsl(var(--text-muted))] mt-0.5">
                                        By <span class="font-medium">{{ $movement->user->name ?? 'System' }}</span>
                                        @if($movement->reference) · Ref: <span class="font-mono">{{ $movement->reference }}</span>@endif
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-sm font-bold tabular-nums {{ $movement->type === 'OUT' ? 'text-[hsl(var(--danger))]' : 'text-[hsl(var(--success))]' }}">
                                        {{ $movement->signedQuantity() }}
                                    </span>
                                    <p class="text-[10px] text-[hsl(var(--text-muted))] mt-0.5">{{ $movement->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm text-[hsl(var(--text-muted))]">No recent activity yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Alerts Panel -->
        <div class="premium-card overflow-hidden flex flex-col">
            <div class="section-header">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="section-title">Action Required</span>
                </div>
                @if($lowStockProducts->count() > 0)
                    <span class="badge badge-warning">{{ $lowStockProducts->count() }}</span>
                @endif
            </div>

            <div class="flex-1 overflow-y-auto divide-y divide-[hsl(var(--border))]">
                @forelse($lowStockProducts as $product)
                    <a href="{{ route('products.show', $product) }}" class="flex items-center justify-between p-4 hover:bg-amber-50/40 transition-colors group">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-[hsl(var(--text-main))] truncate group-hover:text-[hsl(var(--primary))] transition-colors">{{ $product->name }}</p>
                            <p class="text-xs text-[hsl(var(--text-muted))]">{{ $product->sku }}</p>
                        </div>
                        <div class="text-right ml-4 shrink-0">
                            <p class="text-lg font-black tabular-nums {{ $product->quantity <= 0 ? 'text-[hsl(var(--danger))]' : 'text-amber-500' }}">{{ $product->quantity }}</p>
                            <p class="text-[10px] text-[hsl(var(--text-muted))]">min {{ $product->minimum_stock_level }}</p>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-[hsl(var(--success))]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-sm font-semibold text-[hsl(var(--text-main))]">All Good!</p>
                        <p class="text-xs text-[hsl(var(--text-muted))] mt-1">All products are well stocked.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
