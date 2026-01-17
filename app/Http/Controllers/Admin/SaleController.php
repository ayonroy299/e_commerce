<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Sales/Index', [
            'sales' => Sale::with('customer', 'cashier')->latest()->paginate(15)
        ]);
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'lines.product', 'lines.variation', 'payments', 'cashier']);
        return Inertia::render('Admin/Sales/Show', [
            'sale' => $sale
        ]);
    }
}
