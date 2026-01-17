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
        // Simple P&L Snapshot
        $revenue = Account::where('type', 'revenue')->get()->sum('balance');
        $expenses = Account::where('type', 'expense')->get()->sum('balance');
        
        return Inertia::render('Admin/Accounting/Overview', [
            'overview' => [
                'total_revenue' => $revenue,
                'total_expenses' => $expenses,
                'net_profit' => $revenue - $expenses,
                'cash_balance' => Account::where('name', 'Cash')->first()?->balance ?? 0,
            ]
        ]);
    }
}
