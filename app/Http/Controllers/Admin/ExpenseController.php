<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('category', 'user')->latest();
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }
        
        return Inertia::render('Admin/Expenses/Index', [
            'expenses' => $query->paginate(15),
            'categories' => ExpenseCategory::where('status', true)->get(),
            'filters' => $request->only(['start_date', 'end_date'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'details' => 'nullable|string',
        ]);
        
        $validated['branch_id'] = auth()->user()->branch_id;
        $validated['user_id'] = auth()->id();
        $validated['warehouse_id'] = auth()->user()->warehouse_id ?? 'default_warehouse_placeholder';

        $expense = Expense::create($validated);
        
        // Handle attachment if implemented via MediaLibrary later
        
        return redirect()->back()->with('success', 'Expense recorded.');
    }
}
