<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\SSLCommerzService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SSLCommerzController extends Controller
{
    protected $sslService;

    public function __construct(SSLCommerzService $sslService)
    {
        $this->sslService = $sslService;
    }

    public function success(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');

        $validation = $this->sslService->validatePayment($tran_id, $amount, $currency);

        if ($validation) {
            $saleId = $request->input('value_a');
            $sale = Sale::findOrFail($saleId);

            DB::transaction(function () use ($sale, $validation) {
                $sale->update([
                    'payment_status' => 'paid',
                    'transaction_id' => $validation['tran_id'],
                    'payment_gateway' => 'sslcommerz',
                    'gateway_response' => $validation,
                    'status' => 'completed',
                ]);

                // If EMI contract exists and tokenize_id was sent
                if ($sale->emiContract && isset($validation['bank_gw']) && isset($validation['card_no'])) {
                    // In a real scenario, SSLCommerz provides a tokenize_id or similar reference
                    // For this implementation, we store the card info for the auto-debit flag
                    $sale->emiContract->update([
                        'payment_token' => $validation['val_id'], // Using val_id as a reference
                    ]);
                }
            });

            return redirect()->route('sales.show', $sale)->with('success', 'Payment Successful!');
        }

        return redirect()->route('dashboard')->with('error', 'Payment Validation Failed');
    }

    public function fail(Request $request)
    {
        Log::error('SSLCommerz Payment Failed: ' . json_encode($request->all()));
        return redirect()->route('dashboard')->with('error', 'Payment Failed');
    }

    public function cancel(Request $request)
    {
        return redirect()->route('dashboard')->with('info', 'Payment Cancelled');
    }

    public function ipn(Request $request)
    {
        // Instant Payment Notification
        $validation = $this->sslService->validatePayment($request->tran_id, $request->amount, $request->currency);
        
        if ($validation) {
            $sale = Sale::where('invoice_number', $request->tran_id)->first();
            if ($sale && $sale->payment_status !== 'paid') {
                $sale->update([
                    'payment_status' => 'paid',
                    'transaction_id' => $validation['tran_id'],
                    'gateway_response' => $validation,
                ]);
            }
        }
        
        return response()->json(['status' => 'received']);
    }
}
