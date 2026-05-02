<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        // 1. Total counts
        $totalProducts   = Product::active()->count();
        $totalCategories = Category::active()->count();

        // 2. Low stock items
        $lowStockProducts = Product::active()
            ->whereColumn('quantity', '<=', 'minimum_stock_level')
            ->orderBy('quantity', 'asc')
            ->take(5)->get();

        // 3. Out of stock
        $outOfStockCount = Product::active()->where('quantity', '<=', 0)->count();

        // 4. Recent stock activities
        $recentMovements = StockMovement::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(10)->get();

        // 5. Sparkline data — last 7 days movement sums (IN and OUT)
        $days = collect(range(6, 0))->map(fn($d) => Carbon::today()->subDays($d)->toDateString());

        $inByDay = StockMovement::where('type', 'IN')
            ->where('created_at', '>=', Carbon::today()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day, SUM(quantity) as total')
            ->groupBy('day')->pluck('total', 'day');

        $outByDay = StockMovement::where('type', 'OUT')
            ->where('created_at', '>=', Carbon::today()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day, SUM(quantity) as total')
            ->groupBy('day')->pluck('total', 'day');

        $sparkIn     = $days->map(fn($d) => (int)($inByDay[$d]  ?? 0))->values();
        $sparkOut    = $days->map(fn($d) => (int)($outByDay[$d] ?? 0))->values();
        $sparkLabels = $days->map(fn($d) => Carbon::parse($d)->format('D'))->values();

        return view('dashboard', compact(
            'totalProducts', 'totalCategories',
            'lowStockProducts', 'outOfStockCount',
            'recentMovements',
            'sparkIn', 'sparkOut', 'sparkLabels'
        ));
    }
}
