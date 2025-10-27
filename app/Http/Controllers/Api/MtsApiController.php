<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Transaction;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MtsApiController extends Controller
{
    /**
     * МТС Банк API endpoints
     */
    private const MTS_ENDPOINTS = [
        'payment' => '/payments',
        'refund' => '/refunds',
        'status' => '/payments/{orderId}',
        'webhook' => '/webhooks',
    ];

    /**
     * Process payment through MTS Bank
     */
    public function processPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required|string',
            'api_key' => 'required|string',
            'transaction_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'card_number' => 'required|string',
            'expiry_date' => 'required|string',
            'cvv' => 'required|string',
            'cardholder_name' => 'required|string',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $bank = Bank::where('merchant_id', $request->merchant_id)
            ->where('code', 'MTS')
            ->first();
        
        if (!$bank || !$bank->is_active) {
            return response()->json(['error' => 'Invalid MTS merchant credentials'], 401);
        }

        // Check if transaction already exists
        $existingTransaction = Transaction::where('payment_reference', $request->transaction_id)->first();
        if ($existingTransaction) {
            return response()->json(['error' => 'Transaction already exists'], 409);
        }

        try {
            DB::beginTransaction();

            // Prepare MTS API request
            $mtsRequest = $this->prepareMtsPaymentRequest($request, $bank);
            
            // Send request to MTS API
            $mtsResponse = $this->sendMtsRequest($bank, 'payment', $mtsRequest);
            
            if ($mtsResponse['success']) {
                // Find the transaction by payment reference
                $transaction = Transaction::where('payment_reference', $request->transaction_id)->first();
                
                if ($transaction) {
                    $transaction->update([
                        'status' => 'completed',
                        'processed_at' => now(),
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'mts_transaction_id' => $mtsResponse['data']['transactionId'],
                            'mts_order_id' => $mtsResponse['data']['orderId'],
                            'card_last_four' => substr($request->card_number, -4),
                            'cardholder_name' => $request->cardholder_name,
                            'bank_code' => 'MTS',
                        ])
                    ]);

                    // Log audit
                    AuditLog::createLog(
                        'mts_payment_processed',
                        'Transaction',
                        $transaction->id,
                        null,
                        ['status' => 'pending'],
                        ['status' => 'completed']
                    );
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'transaction_id' => $request->transaction_id,
                    'mts_transaction_id' => $mtsResponse['data']['transactionId'],
                    'mts_order_id' => $mtsResponse['data']['orderId'],
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'status' => 'approved',
                    'timestamp' => now()->toISOString(),
                ]);
            } else {
                DB::rollBack();
                
                return response()->json([
                    'success' => false,
                    'transaction_id' => $request->transaction_id,
                    'error' => $mtsResponse['error'],
                    'status' => 'declined',
                    'timestamp' => now()->toISOString(),
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MTS Payment Error: ' . $e->getMessage(), [
                'transaction_id' => $request->transaction_id,
                'merchant_id' => $request->merchant_id,
            ]);
            
            return response()->json(['error' => 'Payment processing failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process refund through MTS Bank
     */
    public function processRefund(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required|string',
            'api_key' => 'required|string',
            'original_transaction_id' => 'required|string',
            'refund_amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $bank = Bank::where('merchant_id', $request->merchant_id)
            ->where('code', 'MTS')
            ->first();
        
        if (!$bank || !$bank->is_active) {
            return response()->json(['error' => 'Invalid MTS merchant credentials'], 401);
        }

        // Find original transaction
        $originalTransaction = Transaction::where('payment_reference', $request->original_transaction_id)->first();
        
        if (!$originalTransaction) {
            return response()->json(['error' => 'Original transaction not found'], 404);
        }

        if ($originalTransaction->status !== 'completed') {
            return response()->json(['error' => 'Cannot refund incomplete transaction'], 400);
        }

        if ($request->refund_amount > $originalTransaction->total_amount) {
            return response()->json(['error' => 'Refund amount exceeds original amount'], 400);
        }

        try {
            DB::beginTransaction();

            // Prepare MTS refund request
            $mtsRequest = $this->prepareMtsRefundRequest($request, $originalTransaction, $bank);
            
            // Send request to MTS API
            $mtsResponse = $this->sendMtsRequest($bank, 'refund', $mtsRequest);

            if ($mtsResponse['success']) {
                // Create refund transaction
                $refundTransaction = Transaction::create([
                    'transaction_id' => 'REF_' . time() . '_' . rand(1000, 9999),
                    'user_id' => $originalTransaction->user_id,
                    'token_id' => $originalTransaction->token_id,
                    'type' => 'refund',
                    'status' => 'completed',
                    'amount' => $request->refund_amount / $originalTransaction->price,
                    'price' => $originalTransaction->price,
                    'total_amount' => $request->refund_amount,
                    'payment_method' => $originalTransaction->payment_method,
                    'payment_reference' => 'REF_' . $request->original_transaction_id,
                    'metadata' => [
                        'original_transaction_id' => $originalTransaction->id,
                        'refund_reason' => $request->reason,
                        'mts_refund_id' => $mtsResponse['data']['refundId'],
                        'bank_code' => 'MTS',
                    ],
                ]);

                // Update original transaction
                $originalTransaction->update([
                    'metadata' => array_merge($originalTransaction->metadata ?? [], [
                        'refunded_amount' => $request->refund_amount,
                        'refund_transaction_id' => $refundTransaction->id,
                    ])
                ]);

                // Log audit
                AuditLog::createLog(
                    'mts_refund_processed',
                    'Transaction',
                    $refundTransaction->id,
                    null,
                    null,
                    $refundTransaction->toArray()
                );

                DB::commit();

                return response()->json([
                    'success' => true,
                    'refund_transaction_id' => $refundTransaction->transaction_id,
                    'original_transaction_id' => $request->original_transaction_id,
                    'mts_refund_id' => $mtsResponse['data']['refundId'],
                    'refund_amount' => $request->refund_amount,
                    'status' => 'approved',
                    'timestamp' => now()->toISOString(),
                ]);
            } else {
                DB::rollBack();
                
                return response()->json([
                    'success' => false,
                    'error' => $mtsResponse['error'],
                    'status' => 'declined',
                    'timestamp' => now()->toISOString(),
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MTS Refund Error: ' . $e->getMessage(), [
                'original_transaction_id' => $request->original_transaction_id,
                'merchant_id' => $request->merchant_id,
            ]);
            
            return response()->json(['error' => 'Refund processing failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get transaction status from MTS Bank
     */
    public function getTransactionStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required|string',
            'api_key' => 'required|string',
            'transaction_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $bank = Bank::where('merchant_id', $request->merchant_id)
            ->where('code', 'MTS')
            ->first();
        
        if (!$bank || !$bank->is_active) {
            return response()->json(['error' => 'Invalid MTS merchant credentials'], 401);
        }

        $transaction = Transaction::where('payment_reference', $request->transaction_id)->first();
        
        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        // Get status from MTS API if we have MTS order ID
        if (isset($transaction->metadata['mts_order_id'])) {
            try {
                $mtsResponse = $this->sendMtsRequest($bank, 'status', [], [
                    'orderId' => $transaction->metadata['mts_order_id']
                ]);

                if ($mtsResponse['success']) {
                    // Update transaction status if it changed
                    $mtsStatus = $this->mapMtsStatusToInternal($mtsResponse['data']['status']);
                    if ($mtsStatus !== $transaction->status) {
                        $transaction->update(['status' => $mtsStatus]);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to get MTS status: ' . $e->getMessage());
            }
        }

        return response()->json([
            'transaction_id' => $transaction->payment_reference,
            'status' => $transaction->status,
            'amount' => $transaction->total_amount,
            'currency' => 'RUB',
            'created_at' => $transaction->created_at->toISOString(),
            'processed_at' => $transaction->processed_at?->toISOString(),
        ]);
    }

    /**
     * Handle MTS webhook notifications
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'orderId' => 'required|string',
            'status' => 'required|string',
            'transactionId' => 'required|string',
            'amount' => 'required|numeric',
            'signature' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid webhook data'], 400);
        }

        // Verify webhook signature
        if (!$this->verifyWebhookSignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        try {
            $transaction = Transaction::where('metadata->mts_order_id', $request->orderId)->first();
            
            if (!$transaction) {
                return response()->json(['error' => 'Transaction not found'], 404);
            }

            $newStatus = $this->mapMtsStatusToInternal($request->status);
            
            if ($newStatus !== $transaction->status) {
                $oldStatus = $transaction->status;
                $transaction->update([
                    'status' => $newStatus,
                    'processed_at' => $newStatus === 'completed' ? now() : null,
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'mts_transaction_id' => $request->transactionId,
                        'webhook_received_at' => now()->toISOString(),
                    ])
                ]);

                // Log audit
                AuditLog::createLog(
                    'mts_webhook_received',
                    'Transaction',
                    $transaction->id,
                    null,
                    ['status' => $oldStatus],
                    ['status' => $newStatus]
                );
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('MTS Webhook Error: ' . $e->getMessage(), [
                'orderId' => $request->orderId,
                'status' => $request->status,
            ]);
            
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Prepare MTS payment request
     */
    private function prepareMtsPaymentRequest(Request $request, Bank $bank): array
    {
        return [
            'merchantId' => $bank->merchant_id,
            'orderId' => $request->transaction_id,
            'amount' => $request->amount * 100, // Convert to kopecks
            'currency' => $request->currency,
            'description' => $request->description ?? 'Payment for tokens',
            'returnUrl' => $bank->settings['success_url'],
            'failUrl' => $bank->settings['fail_url'],
            'notificationUrl' => $bank->settings['notification_url'],
            'cardData' => [
                'pan' => $request->card_number,
                'expiry' => $request->expiry_date,
                'cvv' => $request->cvv,
                'cardholderName' => $request->cardholder_name,
            ],
        ];
    }

    /**
     * Prepare MTS refund request
     */
    private function prepareMtsRefundRequest(Request $request, Transaction $originalTransaction, Bank $bank): array
    {
        return [
            'merchantId' => $bank->merchant_id,
            'originalOrderId' => $originalTransaction->metadata['mts_order_id'] ?? $request->original_transaction_id,
            'refundAmount' => $request->refund_amount * 100, // Convert to kopecks
            'reason' => $request->reason,
        ];
    }

    /**
     * Send request to MTS API
     */
    private function sendMtsRequest(Bank $bank, string $endpoint, array $data = [], array $params = []): array
    {
        $url = $bank->api_endpoint . self::MTS_ENDPOINTS[$endpoint];
        
        // Replace parameters in URL
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }

        $headers = [
            'Authorization' => 'Bearer ' . $bank->api_key,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        try {
            $response = Http::timeout($bank->settings['timeout'] ?? 30)
                ->retry($bank->settings['retry_attempts'] ?? 3)
                ->withHeaders($headers)
                ->post($url, $data);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->json()['message'] ?? 'MTS API error',
                    'status_code' => $response->status(),
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'MTS API request failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Map MTS status to internal status
     */
    private function mapMtsStatusToInternal(string $mtsStatus): string
    {
        $statusMap = [
            'NEW' => 'pending',
            'PENDING' => 'pending',
            'APPROVED' => 'completed',
            'DECLINED' => 'failed',
            'CANCELLED' => 'cancelled',
            'REFUNDED' => 'refunded',
        ];

        return $statusMap[$mtsStatus] ?? 'pending';
    }

    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature(Request $request): bool
    {
        $bank = Bank::where('code', 'MTS')->first();
        if (!$bank) {
            return false;
        }

        $signature = $request->signature;
        $data = $request->except('signature');
        
        // Sort data by keys
        ksort($data);
        
        // Create signature string
        $signatureString = http_build_query($data) . $bank->api_secret;
        
        // Generate expected signature
        $expectedSignature = hash('sha256', $signatureString);
        
        return hash_equals($expectedSignature, $signature);
    }
}


