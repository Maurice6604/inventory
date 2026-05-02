<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(): View
    {
        // 1. Inventory Valuation (Total value of all physical stock)
        $valuation = Product::active()->selectRaw('SUM(quantity * cost_price) as total_cost, SUM(quantity * selling_price) as total_retail')->first();

        // 2. Dead Stock — products older than 60 days with stock but no OUT movements in 60 days.
        //    The 'created_at' filter is critical: it prevents newly-added products from being
        //    incorrectly flagged as dead stock before they have had a chance to sell.
        $sixtyDaysAgo = Carbon::now()->subDays(60);
        $deadStock = Product::active()
            ->where('quantity', '>', 0)
            ->where('created_at', '<', $sixtyDaysAgo)
            ->whereDoesntHave('stockMovements', function ($query) use ($sixtyDaysAgo) {
                $query->where('type', 'OUT')
                      ->where('created_at', '>=', $sixtyDaysAgo);
            })
            ->orderByDesc('quantity')
            ->take(10)
            ->get();

        // 3. Top Movers (Products with the highest volume of 'OUT' movements in the last 30 days)
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $topMovers = Product::active()
            ->whereHas('stockMovements', function ($query) use ($thirtyDaysAgo) {
                $query->where('type', 'OUT')
                      ->where('created_at', '>=', $thirtyDaysAgo);
            })
            ->withSum(['stockMovements as out_volume' => function ($query) use ($thirtyDaysAgo) {
                $query->where('type', 'OUT')
                      ->where('created_at', '>=', $thirtyDaysAgo);
            }], 'quantity')
            ->orderByDesc('out_volume')
            ->take(10)
            ->get();

        // 4. Movement Summary for the last 30 days
        $movementSummary = StockMovement::select('type', DB::raw('SUM(quantity) as total_qty'))
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->groupBy('type')
            ->pluck('total_qty', 'type');

        return view('reports.index', compact('valuation', 'deadStock', 'topMovers', 'movementSummary'));
    }
}
