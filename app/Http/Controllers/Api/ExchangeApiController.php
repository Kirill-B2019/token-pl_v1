<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Token;
use App\Models\Broker;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ExchangeApiController extends Controller
{
    /**
     * Get token price from exchange
     */
    public function getTokenPrice(Request $request): JsonResponse
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

        try {
            // In real implementation, this would call actual exchange API
            $price = $this->fetchPriceFromExchange($token->symbol, $broker->exchange_url);
            
            // Update token price
            $oldPrice = $token->current_price;
            $token->update(['current_price' => $price]);

            // Log audit
            AuditLog::createLog(
                'exchange_price_fetched',
                'Token',
                $token->id,
                null,
                ['current_price' => $oldPrice],
                ['current_price' => $price]
            );

            return response()->json([
                'token_symbol' => $token->symbol,
                'price' => $price,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch price: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Fetch price from exchange (simulation)
     */
    private function fetchPriceFromExchange(string $symbol, string $exchangeUrl): float
    {
        // This is a simulation - in real implementation, this would call actual exchange API
        // For now, return a random price between 0.1 and 1000
        return round(rand(1, 10000) / 10, 2);
    }
}
