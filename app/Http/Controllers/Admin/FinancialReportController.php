<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class FinancialReportController extends Controller
{
    public function dashboard(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        // 1. Total Revenue (Completed Sales)
        $revenue = Sale::whereBetween('sold_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'completed')
            ->sum('total_amount');

        // 2. Refunds/Returns
        // Option A: Use SaleReturn total_amount
        $returns = SaleReturn::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
             ->sum('total_amount');

        // 3. Expenses
        $expenses = Expense::whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        // 4. Net Profit
        $netProfit = ($revenue - $returns) - $expenses;

        return Inertia::render('Admin/Reports/Financial', [
            'stats' => [
                'revenue' => $revenue,
                'returns' => $returns,
                'expenses' => $expenses,
                'net_profit' => $netProfit,
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);
    }
}
