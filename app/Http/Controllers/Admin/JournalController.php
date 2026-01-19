<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Journal;
use App\Models\JournalLine;
use Inertia\Inertia;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $journals = Journal::with(['lines.account', 'creator'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Accounting/Journals/Index', [
            'journals' => $journals,
        ]);
    }

    public function ledger(Account $account)
    {
        $lines = JournalLine::with('journal')
            ->where('account_id', $account->id)
            ->latest()
            ->paginate(50);

        return Inertia::render('Admin/Accounting/Journals/Ledger', [
            'account' => $account,
            'lines' => $lines,
        ]);
    }

    public function overview()
    {
        // Current P&L Totals
        $revenue = Account::where('type', 'revenue')->get()->sum('balance');
        $expenses = Account::where('type', 'expense')->get()->sum('balance');
        
        // Calculate Monthly Trends (Last 6 Months)
        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $monthlyRev = JournalLine::whereHas('journal', fn($q) => $q->whereBetween('date', [$start, $end]))
                ->whereHas('account', fn($q) => $q->where('type', 'revenue'))
                ->get()
                ->sum(fn($l) => $l->credit - $l->debit);

            $monthlyExp = JournalLine::whereHas('journal', fn($q) => $q->whereBetween('date', [$start, $end]))
                ->whereHas('account', fn($q) => $q->where('type', 'expense'))
                ->get()
                ->sum(fn($l) => $l->debit - $l->credit);

            $trends[] = [
                'month' => $date->format('M Y'),
                'revenue' => (float) $monthlyRev,
                'expenses' => (float) $monthlyExp,
            ];
        }

        // Top Expense Categories
        $topExpenses = Account::where('type', 'expense')
            ->get()
            ->map(fn($a) => [
                'name' => $a->name,
                'value' => (float) $a->balance
            ])
            ->sortByDesc('value')
            ->take(5)
            ->values();
        
        return Inertia::render('Admin/Accounting/Overview', [
            'overview' => [
                'total_revenue' => $revenue,
                'total_expenses' => $expenses,
                'net_profit' => $revenue - $expenses,
                'cash_balance' => Account::where('name', 'Cash')->first()?->balance ?? 0,
                'trends' => $trends,
                'top_expenses' => $topExpenses
            ]
        ]);
    }
}
