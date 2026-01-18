<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        return Inertia::render('Admin/Reports/Index');
    }

    public function sales(Request $request)
    {
        $reports = $this->reportService->getSalesReport($request->all());
        return Inertia::render('Admin/Reports/Sales', [
            'reports' => $reports,
            'filters' => $request->only(['branch_id', 'start_date', 'end_date', 'search']),
        ]);
    }

    public function stock(Request $request)
    {
        $reports = $this->reportService->getStockReport($request->all());
        return Inertia::render('Admin/Reports/Stock', [
            'reports' => $reports,
            'filters' => $request->only(['branch_id', 'start_date', 'end_date', 'search']),
        ]);
    }

    public function purchases(Request $request)
    {
        $reports = $this->reportService->getPurchaseReport($request->all());
        return Inertia::render('Admin/Reports/Purchases', [
            'reports' => $reports,
            'filters' => $request->only(['branch_id', 'start_date', 'end_date', 'search']),
        ]);
    }
}
