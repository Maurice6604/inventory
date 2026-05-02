@extends('layouts.app')

@section('title', 'Add Product')
@section('header', 'Add New Product')

@section('content')
<div class="max-w-3xl">
    <div class="premium-card p-6">
        <form method="POST" action="{{ route('products.store') }}">
            @csrf
            
            <div class="space-y-6">
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Product Name <span class="text-[hsl(var(--danger))]">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="premium-input @error('name') border-[hsl(var(--danger))] @enderror">
                        @error('name') <p class="text-xs text-[hsl(var(--danger))] mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">SKU <span class="text-[hsl(var(--danger))]">*</span></label>
                        <input type="text" name="sku" value="{{ old('sku') }}" required class="premium-input @error('sku') border-[hsl(var(--danger))] @enderror">
                        @error('sku') <p class="text-xs text-[hsl(var(--danger))] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Category <span class="text-[hsl(var(--danger))]">*</span></label>
                        <select name="category_id" required class="premium-input @error('category_id') border-[hsl(var(--danger))] @enderror">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-xs text-[hsl(var(--danger))] mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="border-t border-[hsl(var(--border))] pt-6">
                    <h4 class="text-sm font-semibold mb-4 text-[hsl(var(--text-muted))] uppercase tracking-wider">Inventory & Pricing</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-1">Unit / Measurement <span class="text-[hsl(var(--danger))]">*</span></label>
                            <input type="text" name="unit" value="{{ old('unit', 'pcs') }}" placeholder="pcs, kg, box" required class="premium-input @error('unit') border-[hsl(var(--danger))] @enderror">
                            @error('unit') <p class="text-xs text-[hsl(var(--danger))] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Minimum Stock Alert <span class="text-[hsl(var(--danger))]">*</span></label>
                            <input type="number" name="minimum_stock_level" value="{{ old('minimum_stock_level', 10) }}" min="0" required class="premium-input @error('minimum_stock_level') border-[hsl(var(--danger))] @enderror">
                            @error('minimum_stock_level') <p class="text-xs text-[hsl(var(--danger))] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Cost Price <span class="text-[hsl(var(--danger))]">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[hsl(var(--text-muted))]">$</span>
                                <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price') }}" min="0" required class="premium-input pl-8 @error('cost_price') border-[hsl(var(--danger))] @enderror">
                            </div>
                            @error('cost_price') <p class="text-xs text-[hsl(var(--danger))] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Selling Price (Optional)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[hsl(var(--text-muted))]">$</span>
                                <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price') }}" min="0" class="premium-input pl-8 @error('selling_price') border-[hsl(var(--danger))] @enderror">
                            </div>
                            @error('selling_price') <p class="text-xs text-[hsl(var(--danger))] mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-[hsl(var(--border))] pt-6">
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea name="description" rows="3" class="premium-input @error('description') border-[hsl(var(--danger))] @enderror">{{ old('description') }}</textarea>
                    @error('description') <p class="text-xs text-[hsl(var(--danger))] mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-8 flex gap-4 border-t border-[hsl(var(--border))] pt-6">
                <button type="submit" class="btn-primary">Save Product</button>
                <a href="{{ route('products.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
