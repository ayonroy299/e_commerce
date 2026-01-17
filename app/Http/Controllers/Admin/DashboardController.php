<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today()->toDateString();
        $branchId = auth()->user()->branch_id;

        // 1. Daily Stats
        $dailySales = Sale::whereDate('sold_at', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        $dailyOrders = Sale::whereDate('sold_at', $today)
            ->count();

        // 2. Low Stock Alerts
        // Assuming we check the default warehouse stock for simplicity, or sum of all stock
        // For a more complex system, we'd check per warehouse.
        // 2. Low Stock Alerts
        // Check for stocks where quantity is less than the alert_quantity defined *on that specific stock record*
        // AND the alert_quantity is not null.
        $lowStockProducts = Product::whereHas('stocks', function($q) {
                $q->whereColumn('quantity', '<=', 'alert_quantity')
                  ->whereNotNull('alert_quantity');
            })
            ->with(['stocks' => function($q) {
                // We only want the stocks that are low for this product
                $q->whereColumn('quantity', '<=', 'alert_quantity')
                  ->whereNotNull('alert_quantity')
                  ->select('product_id', 'quantity', 'alert_quantity', 'warehouse_id');
            }])
            ->limit(5)
            ->get()
            ->map(function($p) {
                // Sum only the low stock entries for display, or show the first one that triggered it
                $lowStock = $p->stocks->first(); 
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'stock' => $lowStock ? $lowStock->quantity : 0,
                    'threshold' => $lowStock ? $lowStock->alert_quantity : 0
                ];
            });

        // 3. Recent Activity (Latest 5 Sales)
        $recentSales = Sale::with('customer')
            ->latest('sold_at')
            ->limit(5)
            ->get()
            ->map(function($sale) {
                return [
                    'id' => $sale->id,
                    'description' => "Sale #{$sale->invoice_number} to " . ($sale->customer->name ?? 'Walk-in'),
                    'amount' => $sale->total_amount,
                    'time' => $sale->sold_at->diffForHumans(),
                    'type' => 'sale',
                ];
            });
            
        // 4. Currency Symbol
        $currencySymbol = Setting::where('key', 'currency_symbol')
            ->where('branch_id', $branchId)
            ->value('value') ?? '$';

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'daily_sales' => $dailySales,
                'daily_orders' => $dailyOrders,
                'total_products' => Product::count(),
                'currency' => $currencySymbol
            ],
            'low_stock' => $lowStockProducts,
            'recent_activity' => $recentSales // expand with Purchases later if needed
        ]);
    }
}
