<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SSLCommerzService
{
    protected string $storeId;
    protected string $storePassword;
    protected string $apiUrl;
    protected bool $isSandbox;

    public function __construct()
    {
        $this->isSandbox = config('services.sslcommerz.sandbox', true);
        $this->storeId = config('services.sslcommerz.store_id');
        $this->storePassword = config('services.sslcommerz.store_password');
        
        $this->apiUrl = $this->isSandbox 
            ? 'https://sandbox.sslcommerz.com' 
            : 'https://securepay.sslcommerz.com';
    }

    public function initiatePayment(Sale $sale, array $customerInfo)
    {
        $post_data = [
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'total_amount' => $sale->total_amount,
            'currency' => $customerInfo['currency'] ?? 'BDT',
            'tran_id' => $sale->invoice_number,
            'success_url' => route('sslcommerz.success'),
            'fail_url' => route('sslcommerz.fail'),
            'cancel_url' => route('sslcommerz.cancel'),
            'ipn_url' => route('sslcommerz.ipn'),
            
            // Customer Details
            'cus_name' => $customerInfo['name'],
            'cus_email' => $customerInfo['email'] ?? 'customer@example.com',
            'cus_add1' => $customerInfo['address'] ?? 'Customer Address',
            'cus_city' => $customerInfo['city'] ?? 'Dhaka',
            'cus_postcode' => $customerInfo['postcode'] ?? '1000',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $customerInfo['phone'],
            
            // Shipment Details (Optional)
            'shipping_method' => 'NO',
            'num_of_item' => 1,
            'product_name' => 'POS Sale ' . $sale->invoice_number,
            'product_category' => 'General',
            'product_profile' => 'general',
            
            // Tokenization (Required for recurring EMI)
            'multi_card_name' => 'mastercard,visacard,amexcard',
            'value_a' => $sale->id, // Pass sale ID to callback
        ];

        // If it's an EMI setup, enable tokenization
        if ($sale->emiContract && $sale->emiContract->auto_debit) {
            $post_data['tokenize_id'] = 1;
        }

        try {
            $response = Http::asForm()->post($this->apiUrl . '/gwprocess/v4/api.php', $post_data);
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === 'SUCCESS') {
                    return [
                        'status' => 'success',
                        'gateway_url' => $data['GatewayPageURL'],
                    ];
                }
                return [
                    'status' => 'error',
                    'message' => $data['failedreason'] ?? 'Initialization failed',
                ];
            }
            
            return [
                'status' => 'error',
                'message' => 'Service Unavailable',
            ];
        } catch (\Exception $e) {
            Log::error('SSLCommerz Initialisation Error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function validatePayment(string $tranId, string $amount, string $currency)
    {
        $validator_url = $this->apiUrl . '/validator/api/validationserverphp.php';
        
        $params = [
            'val_id' => request('val_id'),
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'format' => 'json',
        ];

        try {
            $response = Http::get($validator_url, $params);
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'VALID' || $data['status'] === 'AUTHENTICATED') {
                    return $data;
                }
            }
            return false;
        } catch (\Exception $e) {
            Log::error('SSLCommerz Validation Error: ' . $e->getMessage());
            return false;
        }
    }
}
