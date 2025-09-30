<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\Token;
use App\Models\Transaction;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BrokerApiController extends Controller
{
    /**
     * Get token balance
     */
    public function getTokenBalance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'token_symbol' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $broker = Broker::where('api_key', $request->api_key)->first();
        
        if (!$broker || !$broker->is_active) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $token = Token::where('symbol', $request->token_symbol)->first();
        
        if (!$token) {
            return response()->json(['error' => 'Token not found'], 404);
        }

        return response()->json([
            'token_symbol' => $token->symbol,
            'available_supply' => $token->available_supply,
            'current_price' => $token->current_price,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Transfer tokens to user
     */
    public function transferTokens(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'user_id' => 'required|integer',
            'token_symbol' => 'required|string',
            'amount' => 'required|numeric|min:0.00000001',
            'transaction_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $broker = Broker::where('api_key', $request->api_key)->first();
        
        if (!$broker || !$broker->is_active) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $token = Token::where('symbol', $request->token_symbol)->first();
        
        if (!$token) {
            return response()->json(['error' => 'Token not found'], 404);
        }

        if (!$token->hasEnoughSupply($request->amount)) {
            return response()->json(['error' => 'Insufficient token supply'], 400);
        }

        try {
            // Create transaction
            $transaction = Transaction::create([
                'transaction_id' => $request->transaction_id,
                'user_id' => $request->user_id,
                'token_id' => $token->id,
                'type' => 'transfer',
                'status' => 'completed',
                'amount' => $request->amount,
                'price' => $token->current_price,
                'total_amount' => $request->amount * $token->current_price,
                'metadata' => [
                    'source' => 'broker_api',
                    'broker_id' => $broker->id,
                ],
            ]);

            // Transfer tokens
            $token->reduceSupply($request->amount);
            
            // Update user balance
            $userBalance = \App\Models\UserBalance::firstOrCreate([
                'user_id' => $request->user_id,
                'token_id' => $token->id,
            ]);
            
            $userBalance->addBalance($request->amount);

            // Log audit
            AuditLog::createLog(
                'api_token_transfer',
                'Transaction',
                $transaction->id,
                null,
                null,
                $transaction->toArray()
            );

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->transaction_id,
                'amount' => $request->amount,
                'token_symbol' => $token->symbol,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Transfer failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'transaction_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $broker = Broker::where('api_key', $request->api_key)->first();
        
        if (!$broker || !$broker->is_active) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $transaction = Transaction::where('transaction_id', $request->transaction_id)->first();
        
        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        return response()->json([
            'transaction_id' => $transaction->transaction_id,
            'status' => $transaction->status,
            'type' => $transaction->type,
            'amount' => $transaction->amount,
            'created_at' => $transaction->created_at->toISOString(),
            'processed_at' => $transaction->processed_at?->toISOString(),
        ]);
    }

    /**
     * Update token price
     */
    public function updateTokenPrice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'token_symbol' => 'required|string',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $broker = Broker::where('api_key', $request->api_key)->first();
        
        if (!$broker || !$broker->is_active) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $token = Token::where('symbol', $request->token_symbol)->first();
        
        if (!$token) {
            return response()->json(['error' => 'Token not found'], 404);
        }

        $oldPrice = $token->current_price;
        $token->update(['current_price' => $request->price]);

        // Log audit
        AuditLog::createLog(
            'api_price_update',
            'Token',
            $token->id,
            null,
            ['current_price' => $oldPrice],
            ['current_price' => $request->price]
        );

        return response()->json([
            'success' => true,
            'token_symbol' => $token->symbol,
            'old_price' => $oldPrice,
            'new_price' => $request->price,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get all tokens
     */
    public function getTokens(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'api_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $broker = Broker::where('api_key', $request->api_key)->first();
        
        if (!$broker || !$broker->is_active) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $tokens = Token::active()->get(['symbol', 'name', 'current_price', 'available_supply']);

        return response()->json([
            'tokens' => $tokens,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
