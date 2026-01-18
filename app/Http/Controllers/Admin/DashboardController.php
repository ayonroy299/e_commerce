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
            'stats' => $stats,
            'filters' => [
                'branch_id' => $branchId,
                'period' => $period,
            ],
            'currency' => $currencySymbol,
        ]);
    }
}
