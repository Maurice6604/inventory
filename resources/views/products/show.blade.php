@extends('layouts.app')

@section('title', $product->name)
@section('header')
<div class="flex items-center gap-3">
    <a href="{{ route('products.index') }}" class="text-[hsl(var(--text-muted))] hover:text-[hsl(var(--primary))]">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    {{ $product->name }}
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Left Column: Product Info & Actions -->
    <div class="space-y-6">
        <!-- Stock Status Card -->
        <div class="premium-card p-6 text-center">
            <h4 class="text-sm font-medium text-[hsl(var(--text-muted))] uppercase tracking-wider mb-2">Current Stock</h4>
            <div class="text-6xl font-black mb-3 {{ $product->quantity <= 0 ? 'text-[hsl(var(--danger))]' : ($product->quantity <= $product->minimum_stock_level ? 'text-[hsl(var(--warning))]' : 'text-[hsl(var(--success))]') }}">
                {{ $product->quantity }}
            </div>
            <div class="mb-6">
                <span class="badge badge-{{ $product->stockStatusClass() }} text-sm px-3 py-1">{{ $product->stockStatus() }}</span>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 gap-3">
                <button onclick="document.getElementById('modal-stock-in').classList.remove('hidden')" class="btn-primary w-full flex justify-center items-center gap-2 !bg-[hsl(var(--success))] hover:!bg-green-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Stock In
                </button>
                <button onclick="document.getElementById('modal-stock-out').classList.remove('hidden')" class="btn-primary w-full flex justify-center items-center gap-2 !bg-[hsl(var(--danger))] hover:!bg-red-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                    Stock Out
                </button>
            </div>
            @if(Auth::user()->isAdmin())
            <div class="mt-3">
                <button onclick="document.getElementById('modal-adjust').classList.remove('hidden')" class="btn-secondary w-full flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Manual Adjust (Admin)
                </button>
            </div>
            @endif
        </div>

        <!-- Product Details -->
        <div class="premium-card p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Details</h3>
                <!-- Edit button would go here -->
            </div>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-[hsl(var(--text-muted))]">SKU</p>
                    <p class="font-medium font-mono">{{ $product->sku }}</p>
                </div>
                <div>
                    <p class="text-sm text-[hsl(var(--text-muted))]">Category</p>
                    <p class="font-medium"><span class="badge badge-secondary">{{ $product->category->name }}</span></p>
                </div>
                <div>
                    <p class="text-sm text-[hsl(var(--text-muted))]">Added By</p>
                    <p class="font-medium">{{ $product->creator->name ?? 'System' }}</p>
                </div>
                <div>
                    <p class="text-sm text-[hsl(var(--text-muted))]">Unit / Packaging</p>
                    <p class="font-medium">{{ $product->unit }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-[hsl(var(--text-muted))]">Cost Price</p>
                        <p class="font-medium">${{ number_format($product->cost_price, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-[hsl(var(--text-muted))]">Selling Price</p>
                        <p class="font-medium">${{ number_format($product->selling_price, 2) }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-[hsl(var(--text-muted))]">Alert Threshold</p>
                    <p class="font-medium text-[hsl(var(--warning))]">{{ $product->minimum_stock_level }} units</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Audit Trail -->
    <div class="lg:col-span-2 premium-card overflow-hidden flex flex-col">
        <div class="p-6 border-b border-[hsl(var(--border))] flex justify-between items-center bg-white/50">
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 text-[hsl(var(--primary))]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Stock Audit Trail
            </h3>
            <a href="{{ route('stock.history', ['product_id' => $product->id]) }}" class="text-sm font-medium text-[hsl(var(--primary))] hover:underline">View All</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type & Source</th>
                        <th>Change</th>
                        <th>Balance</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[hsl(var(--border))]">
                    @forelse($movements as $movement)
                        <tr>
                            <td class="text-sm">
                                <div class="font-medium">{{ $movement->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-[hsl(var(--text-muted))]">{{ $movement->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div class="mb-1"><span class="badge badge-{{ $movement->typeBadgeClass() }}">{{ $movement->typeLabel() }}</span></div>
                                <div class="text-xs text-[hsl(var(--text-muted))]">{{ $movement->source ?? 'Manual' }} <br/> {{ $movement->reference }}</div>
                            </td>
                            <td>
                                <span class="font-bold {{ $movement->type === 'OUT' ? 'text-[hsl(var(--danger))]' : 'text-[hsl(var(--success))]' }}">
                                    {{ $movement->signedQuantity() }}
                                </span>
                            </td>
                            <td class="font-medium font-mono">
                                {{ $movement->quantity_after }}
                            </td>
                            <td class="text-sm">
                                {{ $movement->user->name ?? 'System' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-[hsl(var(--text-muted))]">No movements recorded yet.</td>
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

<!-- Modals -->

<!-- Stock IN Modal -->
<div id="modal-stock-in" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="premium-card w-full max-w-md p-6 animate-fade-in">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-[hsl(var(--success))]">Stock In</h3>
            <button onclick="document.getElementById('modal-stock-in').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form method="POST" action="{{ route('stock.in', $product) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Quantity to Add</label>
                    <input type="number" name="quantity" min="1" required class="premium-input text-lg" placeholder="e.g. 50">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Source / Vendor</label>
                    <input type="text" name="source" required class="premium-input" placeholder="e.g. Supplier XYZ">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Reference (PO #)</label>
                    <input type="text" name="reference" class="premium-input" placeholder="Optional">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Notes</label>
                    <textarea name="notes" class="premium-input" rows="2"></textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full !bg-[hsl(var(--success))]">Confirm Stock In</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Stock OUT Modal -->
<div id="modal-stock-out" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="premium-card w-full max-w-md p-6 animate-fade-in">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-[hsl(var(--danger))]">Stock Out</h3>
            <button onclick="document.getElementById('modal-stock-out').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form method="POST" action="{{ route('stock.out', $product) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Quantity to Remove</label>
                    <input type="number" name="quantity" min="1" max="{{ $product->quantity }}" required class="premium-input text-lg" placeholder="Max: {{ $product->quantity }}">
                    <p class="text-xs text-[hsl(var(--text-muted))] mt-1">Current available: {{ $product->quantity }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Reason / Destination</label>
                    <input type="text" name="source" required class="premium-input" placeholder="e.g. Store front, Damaged, Usage">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Reference (Req #)</label>
                    <input type="text" name="reference" class="premium-input" placeholder="Optional">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Notes</label>
                    <textarea name="notes" class="premium-input" rows="2"></textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full !bg-[hsl(var(--danger))]">Confirm Stock Out</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Adjust Modal -->
<div id="modal-adjust" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="premium-card w-full max-w-md p-6 animate-fade-in border-t-4 border-[hsl(var(--warning))]">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-[hsl(var(--warning))]">Admin Adjustment</h3>
            <button onclick="document.getElementById('modal-adjust').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <div class="bg-yellow-50 text-yellow-800 text-sm p-3 rounded-lg mb-4">
            Warning: This forces the absolute stock value and generates an adjustment audit record.
        </div>
        <form method="POST" action="{{ route('stock.adjust', $product) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">New Exact Total Quantity</label>
                    <input type="number" name="new_quantity" min="0" required class="premium-input text-lg font-mono" value="{{ $product->quantity }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Reason</label>
                    <select name="reason" required class="premium-input">
                        <option value="Inventory Count">Inventory Count (Audit)</option>
                        <option value="Shrinkage">Shrinkage / Loss</option>
                        <option value="System Correction">System Correction</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Mandatory Notes</label>
                    <textarea name="notes" required class="premium-input" rows="2" placeholder="Explain why this adjustment is necessary..."></textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full !bg-[hsl(var(--warning))] !text-yellow-900">Force Update Stock</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
