<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function __invoke(Request $request)
    {
        $branchId = auth()->user()->is_super_admin ? $request->get('branch_id') : auth()->user()->branch_id;
        $period = $request->get('period', 'today');

        $stats = $this->dashboardService->getStats($branchId, $period);

        $currencySymbol = Setting::where('key', 'currency_symbol')
            ->where('branch_id', auth()->user()->branch_id)
            ->value('value') ?? '$';

        return Inertia::render('Admin/Dashboard', [
            'stats' => array_merge($stats['sales'], [
                'total_products' => $stats['inventory']['total_products'] ?? 0,
                'low_stock_count' => $stats['inventory']['low_stock_count'] ?? 0,
                'lifetime_sales' => $stats['lifetime_sales'] ?? 0,
                'currency' => $currencySymbol
            ]),
            'income_stats' => $stats['income_stats'],
            'traffic_stats' => $stats['traffic_stats'],
            'recent_orders' => $stats['recent_orders'],
            'recent_reviews' => $stats['recent_reviews'],
            'active_users' => $stats['active_users'],
            'sales_trend' => $stats['sales_trend'],
            'top_products' => $stats['top_products'],
            'low_stock' => $stats['inventory']['low_stock_items'] ?? [],
            'filters' => [
                'branch_id' => $branchId,
                'period' => $period,
            ],
        ]);
    }
}
