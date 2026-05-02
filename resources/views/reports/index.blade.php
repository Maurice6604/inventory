@extends('layouts.app')

@section('title', 'Analytics & Reports')
@section('header', 'Business Intelligence')

@section('breadcrumbs')
    <span class="text-gray-300">/</span>
    <span class="text-[hsl(var(--text-main))] font-medium">Business Reports</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Valuation Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="premium-card p-6 bg-gradient-to-br from-white to-gray-50 border-l-4 border-[hsl(var(--primary))]">
            <p class="text-sm font-semibold text-[hsl(var(--text-muted))] uppercase tracking-wider mb-2">Total Warehouse Cost</p>
            <h3 class="text-4xl font-black text-[hsl(var(--text-main))]">${{ number_format($valuation->total_cost, 2) }}</h3>
            <p class="text-xs text-[hsl(var(--text-muted))] mt-2">Capital tied up in current inventory.</p>
        </div>

        <div class="premium-card p-6 bg-gradient-to-br from-white to-gray-50 border-l-4 border-[hsl(var(--success))]">
            <p class="text-sm font-semibold text-[hsl(var(--text-muted))] uppercase tracking-wider mb-2">Potential Retail Value</p>
            <h3 class="text-4xl font-black text-[hsl(var(--success))]">${{ number_format($valuation->total_retail, 2) }}</h3>
            <p class="text-xs text-[hsl(var(--text-muted))] mt-2">Expected revenue if all stock is sold.</p>
        </div>

        <div class="premium-card p-6 bg-gradient-to-br from-white to-gray-50 border-l-4 border-indigo-400">
            <p class="text-sm font-semibold text-[hsl(var(--text-muted))] uppercase tracking-wider mb-2">Estimated Gross Profit</p>
            <h3 class="text-4xl font-black text-indigo-600">${{ number_format($valuation->total_retail - $valuation->total_cost, 2) }}</h3>
            <p class="text-xs text-[hsl(var(--text-muted))] mt-2">Projected margin upon full liquidation.</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Movers Bar Chart -->
        <div class="lg:col-span-2 premium-card overflow-hidden">
            <div class="section-header">
                <span class="section-title">Top Movers — 30 Day Volume</span>
            </div>
            <div class="p-6">
                <canvas id="topMoversChart" height="120"></canvas>
            </div>
        </div>
        <!-- Valuation Donut -->
        <div class="premium-card overflow-hidden">
            <div class="section-header">
                <span class="section-title">Valuation Breakdown</span>
            </div>
            <div class="p-6 flex items-center justify-center">
                <canvas id="valuationDonut" height="180" width="180"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Top Movers -->
        <div class="premium-card overflow-hidden flex flex-col">
            <div class="p-6 border-b border-[hsl(var(--border))] flex justify-between items-center bg-white/50">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-[hsl(var(--success))]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    Top Movers (Last 30 Days)
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th class="text-right">Units Out</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[hsl(var(--border))]">
                        @forelse($topMovers as $product)
                            <tr class="hover:bg-gray-50/50">
                                <td>
                                    <a href="{{ route('products.show', $product) }}" class="font-medium hover:text-[hsl(var(--primary))]">{{ $product->name }}</a>
                                    <div class="text-xs text-[hsl(var(--text-muted))]">{{ $product->sku }}</div>
                                </td>
                                <td><span class="badge badge-secondary">{{ $product->category->name ?? 'N/A' }}</span></td>
                                <td class="text-right font-bold text-[hsl(var(--success))]">{{ $product->out_volume }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-6 text-[hsl(var(--text-muted))]">No sales/outbound data in the last 30 days.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Dead Stock -->
        <div class="premium-card overflow-hidden flex flex-col border-t-4 border-[hsl(var(--danger))]">
            <div class="p-6 border-b border-[hsl(var(--border))] flex justify-between items-center bg-white/50">
                <h3 class="text-lg font-semibold flex items-center gap-2 text-[hsl(var(--danger))]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4M12 20V4"></path></svg>
                    Dead Stock Alert
                </h3>
            </div>
            <div class="px-6 py-3 bg-red-50 text-xs text-red-700 font-medium">
                Products taking up space with 0 outbound movement in over 60 days.
            </div>
            <div class="overflow-x-auto">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock Level</th>
                            <th class="text-right">Tied Up Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[hsl(var(--border))]">
                        @forelse($deadStock as $product)
                            <tr class="hover:bg-gray-50/50">
                                <td>
                                    <a href="{{ route('products.show', $product) }}" class="font-medium text-[hsl(var(--danger))] hover:underline">{{ $product->name }}</a>
                                    <div class="text-xs text-[hsl(var(--text-muted))]">{{ $product->sku }}</div>
                                </td>
                                <td><span class="font-mono">{{ $product->quantity }}</span></td>
                                <td class="text-right font-medium">${{ number_format($product->quantity * $product->cost_price, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-8 text-[hsl(var(--text-muted))]">Excellent! You have no dead stock right now.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Top Movers Bar Chart
    const topMoversLabels = @json($topMovers->pluck('name'));
    const topMoversData   = @json($topMovers->pluck('out_volume'));
    if (document.getElementById('topMoversChart') && topMoversLabels.length) {
        new Chart(document.getElementById('topMoversChart'), {
            type: 'bar',
            data: {
                labels: topMoversLabels,
                datasets: [{ label: 'Units Out', data: topMoversData,
                    backgroundColor: 'rgba(34,197,94,0.15)', borderColor: '#16a34a',
                    borderWidth: 2, borderRadius: 6 }]
            },
            options: {
                indexAxis: 'y', responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } }, y: { grid: { display: false } } }
            }
        });
    }

    // Valuation Donut
    const cost   = {{ $valuation->total_cost   ?? 0 }};
    const retail = {{ $valuation->total_retail  ?? 0 }};
    const profit = Math.max(0, retail - cost);
    if (document.getElementById('valuationDonut')) {
        new Chart(document.getElementById('valuationDonut'), {
            type: 'doughnut',
            data: {
                labels: ['Cost', 'Gross Profit'],
                datasets: [{ data: [cost, profit],
                    backgroundColor: ['#6366f1', '#22c55e'],
                    borderColor: ['#fff','#fff'], borderWidth: 3 }]
            },
            options: {
                cutout: '68%', responsive: false,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
            }
        });
    }
});
</script>
@endpush
