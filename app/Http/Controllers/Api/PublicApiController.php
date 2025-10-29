<?php
// |KB Публичный API: список токенов, пакетов, банков и брокеров

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Broker;
use App\Models\Token;
use App\Models\TokenPackage;
use Illuminate\Http\JsonResponse;

class PublicApiController extends Controller
{
    /**
     * Get list of active tokens with pricing and supply
     */
    public function tokens(): JsonResponse
    {
        $tokens = Token::active()->get([
            'symbol', 'name', 'current_price', 'total_supply', 'available_supply'
        ]);

        return response()->json([
            'tokens' => $tokens,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get a single token by symbol
     */
    public function token(string $symbol): JsonResponse
    {
        $token = Token::active()->where('symbol', $symbol)->first();
        if (!$token) {
            return response()->json(['error' => 'Token not found'], 404);
        }

        return response()->json([
            'token' => $token->only(['symbol', 'name', 'current_price', 'total_supply', 'available_supply', 'metadata']),
        ]);
    }

    /**
     * Get list of active token packages
     */
    public function tokenPackages(): JsonResponse
    {
        $packages = TokenPackage::active()->ordered()->get([
            'id', 'name', 'description', 'token_amount', 'price', 'discount_percentage'
        ])->map(function (TokenPackage $pkg) {
            return [
                'id' => $pkg->id,
                'name' => $pkg->name,
                'description' => $pkg->description,
                'token_amount' => $pkg->token_amount,
                'price' => $pkg->price,
                'discount_percentage' => $pkg->discount_percentage,
                'final_price' => $pkg->final_price,
                'savings_amount' => $pkg->savings_amount,
            ];
        });

        return response()->json([
            'packages' => $packages,
            'count' => $packages->count(),
        ]);
    }

    /**
     * Get list of active banks (sanitized/public info only)
     */
    public function banks(): JsonResponse
    {
        $banks = Bank::active()->get(['id', 'name', 'code']);

        return response()->json([
            'banks' => $banks,
        ]);
    }

    /**
     * Get list of active brokers (sanitized/public info only)
     */
    public function brokers(): JsonResponse
    {
        $brokers = Broker::active()->get(['id', 'name']);

        return response()->json([
            'brokers' => $brokers,
        ]);
    }
}


