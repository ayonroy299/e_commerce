<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Expenses/Categories/Index', [
            'categories' => ExpenseCategory::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'boolean',
        ]);
        
        $validated['branch_id'] = auth()->user()->branch_id;

        ExpenseCategory::create($validated);

        return redirect()->back()->with('success', 'Category created.');
    }
    
    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
         $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'boolean',
        ]);
        
        $expenseCategory->update($validated);
        
        return redirect()->back()->with('success', 'Category updated.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->delete();
        return redirect()->back()->with('success', 'Category deleted.');
    }
}
