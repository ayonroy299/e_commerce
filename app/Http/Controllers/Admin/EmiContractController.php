<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmiContract;
use App\Services\EmiService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmiContractController extends Controller
{
    public function index(Request $request)
    {
        $contracts = EmiContract::with(['sale.customer', 'plan', 'creator'])
            ->latest()
            ->paginate($request->input('per_page', 15));

        return Inertia::render('Admin/Emi/Contracts/Index', [
            'items' => $contracts,
        ]);
    }

    public function show(EmiContract $contract)
    {
        $contract->load(['sale.customer', 'plan', 'schedules', 'receipts.paymentMethod', 'creator']);

        return Inertia::render('Admin/Emi/Contracts/Show', [
            'contract' => $contract,
            'paymentMethods' => \App\Models\PaymentMethod::where('is_active', true)->get(),
        ]);
    }

    public function cancel(EmiContract $contract)
    {
        $contract->update(['status' => 'cancelled']);
        return back()->with('success', 'Contract cancelled successfully');
    }
}
