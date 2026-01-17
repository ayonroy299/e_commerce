<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmiContract;
use App\Services\EmiService;
use Illuminate\Http\Request;

class EmiReceiptController extends Controller
{
    protected $emiService;

    public function __construct(EmiService $emiService)
    {
        $this->emiService = $emiService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'emi_contract_id' => 'required|exists:emi_contracts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'reference_no' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        $contract = EmiContract::findOrFail($request->emi_contract_id);
        
        $this->emiService->processPayment(
            $contract,
            $request->amount,
            $request->payment_method_id,
            $request->reference_no,
            $request->note
        );

        return back()->with('success', 'Payment processed successfully');
    }
}
