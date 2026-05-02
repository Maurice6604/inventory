<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with('category')->active();

        // Search by name or SKU
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->inCategory($request->category_id);
        }

        // Filter by low stock
        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Category::active()->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::active()->orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:200',
            'sku' => 'required|string|max:100|unique:products',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'minimum_stock_level' => 'required|integer|min:0',
        ]);

        $validated['created_by'] = auth()->id();

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        // Load the product with its category, creator, and latest 10 stock movements
        $product->load(['category', 'creator']);
        $movements = $product->stockMovements()->with('user')->paginate(10);
        
        return view('products.show', compact('product', 'movements'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::active()->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:200',
            'sku' => 'required|string|max:100|unique:products,sku,' . $product->id,
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'minimum_stock_level' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->route('products.show', $product)->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // We allow soft deleting a product. The DB restricts hard deletes via FKs.
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
