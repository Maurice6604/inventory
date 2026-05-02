@extends('layouts.app')

@section('title', 'Edit Category')
@section('header', 'Edit Category: ' . $category->name)

@section('content')
<div class="max-w-xl">
    <div class="premium-card p-6">
        <form method="POST" action="{{ route('categories.update', $category) }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required class="premium-input @error('name') border-[hsl(var(--danger))] @enderror">
                    @error('name') <p class="text-xs text-[hsl(var(--danger))] mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea name="description" rows="3" class="premium-input">{{ old('description', $category->description) }}</textarea>
                </div>
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="rounded border-[hsl(var(--border))] text-[hsl(var(--primary))] focus:ring-[hsl(var(--primary))]">
                        Category is Active
                    </label>
                </div>
                <div class="pt-4 flex gap-4 border-t border-[hsl(var(--border))] mt-6">
                    <button type="submit" class="btn-primary">Update Category</button>
                    <a href="{{ route('categories.index') }}" class="btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
