@extends('layouts.app')

@section('title', 'Categories')
@section('header', 'Manage Categories')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Left Column: Add Category Form -->
    <div class="premium-card p-6 h-fit">
        <h3 class="text-lg font-semibold mb-4 border-b border-[hsl(var(--border))] pb-2">Add Category</h3>
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="premium-input @error('name') border-[hsl(var(--danger))] @enderror" placeholder="e.g. Electronics">
                    @error('name') <p class="text-xs text-[hsl(var(--danger))] mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea name="description" rows="3" class="premium-input">{{ old('description') }}</textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full">Create Category</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Right Column: Categories List -->
    <div class="lg:col-span-2 premium-card overflow-hidden flex flex-col">
        <div class="p-6 border-b border-[hsl(var(--border))] bg-white/50">
            <h3 class="text-lg font-semibold text-[hsl(var(--text-main))]">Categories List</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th>Products</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[hsl(var(--border))]">
                    @forelse($categories as $category)
                        <tr>
                            <td>
                                <div class="font-semibold text-[hsl(var(--text-main))]">{{ $category->name }}</div>
                                @if($category->description)
                                    <div class="text-xs text-[hsl(var(--text-muted))] truncate max-w-xs">{{ $category->description }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $category->active_products_count > 0 ? 'badge-success' : 'badge-secondary' }}">{{ $category->active_products_count }} items</span>
                            </td>
                            <td class="text-right space-x-2">
                                <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-[hsl(var(--danger))] hover:underline text-sm font-medium" {{ $category->active_products_count > 0 ? 'disabled' : '' }} title="{{ $category->active_products_count > 0 ? 'Cannot delete categories with active products' : '' }}">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-8 text-[hsl(var(--text-muted))]">No categories defined yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="p-4 border-t border-[hsl(var(--border))] bg-gray-50/50">
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection
