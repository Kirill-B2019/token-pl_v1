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

class BankApiController extends Controller
{
    /**
     * Process payment
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
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $bank = Bank::where('merchant_id', $request->merchant_id)
            ->where('api_key', $request->api_key)
            ->first();
        
        if (!$bank || !$bank->is_active) {
            return response()->json(['error' => 'Invalid merchant credentials'], 401);
        }

        // Check if transaction already exists
        $existingTransaction = Transaction::where('payment_reference', $request->transaction_id)->first();
        if ($existingTransaction) {
            return response()->json(['error' => 'Transaction already exists'], 409);
        }

        try {
            DB::beginTransaction();

            // Simulate payment processing
            $paymentStatus = $this->simulatePaymentProcessing($request);
            
            if ($paymentStatus['success']) {
                // Find the transaction by payment reference
                $transaction = Transaction::where('payment_reference', $request->transaction_id)->first();
                
                if ($transaction) {
                    $transaction->update([
                        'status' => 'completed',
                        'processed_at' => now(),
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'bank_transaction_id' => $paymentStatus['bank_transaction_id'],
                            'card_last_four' => substr($request->card_number, -4),
                            'cardholder_name' => $request->cardholder_name,
                        ])
                    ]);

                    // Log audit
                    AuditLog::createLog(
                        'payment_processed',
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
                    'bank_transaction_id' => $paymentStatus['bank_transaction_id'],
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
                    'error' => $paymentStatus['error'],
                    'status' => 'declined',
                    'timestamp' => now()->toISOString(),
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json(['error' => 'Payment processing failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process refund
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
            ->where('api_key', $request->api_key)
            ->first();
        
        if (!$bank || !$bank->is_active) {
            return response()->json(['error' => 'Invalid merchant credentials'], 401);
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
                    'bank_transaction_id' => 'REF_' . time(),
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
                'refund_processed',
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
                'refund_amount' => $request->refund_amount,
                'status' => 'approved',
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json(['error' => 'Refund processing failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Verify transaction
     */
    public function verifyTransaction(Request $request): JsonResponse
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
            ->where('api_key', $request->api_key)
            ->first();
        
        if (!$bank || !$bank->is_active) {
            return response()->json(['error' => 'Invalid merchant credentials'], 401);
        }

        $transaction = Transaction::where('payment_reference', $request->transaction_id)->first();
        
        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        return response()->json([
            'transaction_id' => $transaction->payment_reference,
            'status' => $transaction->status,
            'amount' => $transaction->total_amount,
            'currency' => 'USD', // Default currency
            'created_at' => $transaction->created_at->toISOString(),
            'processed_at' => $transaction->processed_at?->toISOString(),
        ]);
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required|string',
            'api_key' => 'required|string',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after:from_date',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $bank = Bank::where('merchant_id', $request->merchant_id)
            ->where('api_key', $request->api_key)
            ->first();
        
        if (!$bank || !$bank->is_active) {
            return response()->json(['error' => 'Invalid merchant credentials'], 401);
        }

        $query = Transaction::whereNotNull('payment_reference');

        if ($request->from_date) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->where('created_at', '<=', $request->to_date);
        }

        $transactions = $query->latest()
            ->limit($request->limit ?? 50)
            ->get(['transaction_id', 'payment_reference', 'type', 'status', 'total_amount', 'created_at', 'processed_at']);

        return response()->json([
            'transactions' => $transactions,
            'count' => $transactions->count(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Simulate payment processing
     */
    private function simulatePaymentProcessing(Request $request): array
    {
        // This is a simulation - in real implementation, this would integrate with actual payment gateway
        $cardNumber = $request->card_number;
        $cvv = $request->cvv;
        
        // Simple validation simulation
        if (strlen($cardNumber) < 16 || strlen($cvv) < 3) {
            return [
                'success' => false,
                'error' => 'Invalid card details'
            ];
        }

        // Simulate random failures (5% chance)
        if (rand(1, 100) <= 5) {
            return [
                'success' => false,
                'error' => 'Payment declined by bank'
            ];
        }

        return [
            'success' => true,
            'bank_transaction_id' => 'BANK_' . time() . '_' . rand(100000, 999999),
        ];
    }
}
