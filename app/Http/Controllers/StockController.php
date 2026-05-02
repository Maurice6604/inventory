<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class StockController extends Controller
{
    protected StockMovementService $stockService;

    public function __construct(StockMovementService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Show global stock movement history.
     */
    public function history(Request $request): View
    {
        $query = StockMovement::with(['product' => function($q) {
            $q->withTrashed();
        }, 'user' => function($q) {
            $q->withTrashed();
        }]);

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('product_id')) {
            $query->forProduct($request->product_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->betweenDates($request->date_from, $request->date_to);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $products = Product::active()->orderBy('name')->get();

        return view('stock.history', compact('movements', 'products'));
    }

    /**
     * Show the form to manage stock for a specific product.
     */
    public function manage(Product $product): View
    {
        $product->load('category');
        return view('stock.manage', compact('product'));
    }

    /**
     * Process stock IN.
     */
    public function stockIn(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'source' => 'required|string|max:100',
            'reference' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->stockService->addStock(
                $product,
                $request->quantity,
                $request->user()->id,
                $request->source,
                $request->reference,
                $request->notes
            );

            return redirect()->route('products.show', $product)->with('success', "Added {$request->quantity} units to stock.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Process stock OUT.
     */
    public function stockOut(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'source' => 'required|string|max:100',
            'reference' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->stockService->removeStock(
                $product,
                $request->quantity,
                $request->user()->id,
                $request->source,
                $request->reference,
                $request->notes
            );

            return redirect()->route('products.show', $product)->with('success', "Removed {$request->quantity} units from stock.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Process stock ADJUSTMENT (Admin only).
     */
    public function adjust(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'new_quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:100',
            'reference' => 'nullable|string|max:200',
            'notes' => 'required|string', // Notes are required for adjustments
        ]);

        try {
            $movement = $this->stockService->adjustStock(
                $product,
                $request->new_quantity,
                $request->user()->id,
                $request->reason,
                $request->reference,
                $request->notes
            );

            if ($movement) {
                return redirect()->route('products.show', $product)->with('success', "Stock manually adjusted to {$request->new_quantity}.");
            }

            return redirect()->route('products.show', $product)->with('info', "No change made. Stock was already {$request->new_quantity}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Return last 3 stock movements as JSON for the hover quick-view popover.
     */
    public function preview(Product $product)
    {
        $movements = $product->stockMovements()
            ->with('user')
            ->take(3)
            ->get()
            ->map(fn($m) => [
                'type'            => $m->type,
                'signed_quantity' => $m->signedQuantity(),
                'ago'             => $m->created_at->diffForHumans(),
            ]);

        return response()->json($movements);
    }
}
