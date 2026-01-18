<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get statistics for a specific branch or all branches if null.
     */
    public function getStats(?int $branchId = null, string $period = 'today')
    {
        $cacheKey = "dashboard_stats_" . ($branchId ?? 'all') . "_" . $period;

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($branchId, $period) {
            $dateRange = $this->getDateRange($period);

            return [
                'sales' => $this->getSalesStats($branchId, $dateRange),
                'inventory' => $this->getInventoryStats($branchId),
                'purchases' => $this->getPurchaseStats($branchId, $dateRange),
                'top_products' => $this->getTopProducts($branchId, $dateRange),
                'recent_activity' => $this->getRecentActivity($branchId),
            ];
        });
    }

    protected function getSalesStats(?int $branchId, array $range)
    {
        $query = Sale::whereBetween('sold_at', $range)
            ->where('status', 'completed');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return [
            'total_amount' => $query->sum('total_amount'),
            'order_count' => $query->count(),
            'avg_order_value' => $query->avg('total_amount') ?? 0,
        ];
    }

    protected function getInventoryStats(?int $branchId)
    {
        $lowStockQuery = Product::with(['stocks' => function ($q) use ($branchId) {
            if ($branchId) $q->where('branch_id', $branchId);
        }])->whereHas('stocks', function ($q) use ($branchId) {
            $q->whereColumn('quantity', '<=', 'alert_quantity')
              ->whereNotNull('alert_quantity');
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        });

        $lowStockItems = $lowStockQuery->limit(5)->get()->map(function ($p) {
            $stock = $p->stocks->first(); // Assuming single stock entry for simplicity or sum if needed
            return [
                'id' => $p->id,
                'name' => $p->name,
                'stock' => $stock ? $stock->quantity : 0,
                'threshold' => $stock ? $stock->alert_quantity : 0,
            ];
        });

        return [
            'total_products' => Product::count(),
            'low_stock_count' => $lowStockQuery->count(),
            'low_stock_items' => $lowStockItems,
        ];
    }

    protected function getPurchaseStats(?int $branchId, array $range)
    {
        $query = PurchaseOrder::whereBetween('created_at', $range)
            ->where('status', 'completed');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return [
            'total_amount' => $query->sum('total_amount'),
            'order_count' => $query->count(),
        ];
    }

    protected function getTopProducts(?int $branchId, array $range)
    {
        return DB::table('sale_lines')
            ->join('sales', 'sale_lines.sale_id', '=', 'sales.id')
            ->join('products', 'sale_lines.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(sale_lines.quantity) as total_qty'), DB::raw('SUM(sale_lines.subtotal) as total_revenue'))
            ->when($branchId, fn($q) => $q->where('sales.branch_id', $branchId))
            ->whereBetween('sales.sold_at', $range)
            ->where('sales.status', 'completed')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();
    }

    protected function getRecentActivity(?int $branchId)
    {
        $sales = Sale::with('customer')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->latest('sold_at')
            ->limit(10)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'type' => 'sale',
                'description' => "Sale #{$s->invoice_number} to " . ($s->customer->name ?? 'Walk-in'),
                'amount' => $s->total_amount,
                'time' => $s->sold_at->diffForHumans(),
            ]);

        return $sales;
    }

    protected function getDateRange(string $period): array
    {
        return match ($period) {
            'today' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            default => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
        };
    }
}
