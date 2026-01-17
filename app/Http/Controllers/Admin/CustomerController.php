<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Customer::query()->latest();

        if ($search) {
             $query->where(function($q) use ($search) {
                 $q->where('name', 'like', "%{$search}%")
                   ->orWhere('phone', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%");
             });
        }
        
        if ($request->wantsJson()) {
            return $query->limit(20)->get();
        }

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $query->paginate(15),
            'filters' => ['search' => $search]
        ]);
    }
    
    public function create()
    {
        return Inertia::render('Admin/Customers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'opening_balance' => 'numeric|min:0',
        ]);
        
        $validated['branch_id'] = auth()->user()->branch_id;

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer created.');
    }
    
    // Minimal CRUD for this sprint
    public function show(Customer $customer)
    {
        return Inertia::render('Admin/Customers/Show', ['customer' => $customer]);
    }
}
