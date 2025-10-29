<?php
// |KB Пользовательский API: профиль, балансы и транзакции текущего пользователя

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\UserBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserApiController extends Controller
{
    /**
     * Get current authenticated user info
     */
    public function me(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
        ]);
    }

    /**
     * Get user balances grouped by token
     */
    public function balances(): JsonResponse
    {
        $user = Auth::user();
        $balances = UserBalance::with('token:id,symbol,name')
            ->where('user_id', $user->id)
            ->get()
            ->map(function (UserBalance $b) {
                return [
                    'token_symbol' => $b->token?->symbol,
                    'token_name' => $b->token?->name,
                    'balance' => $b->balance,
                    'locked_balance' => $b->locked_balance,
                    'available_balance' => $b->available_balance,
                ];
            });

        return response()->json([
            'balances' => $balances,
        ]);
    }

    /**
     * Get user transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|in:pending,completed,failed,cancelled,refunded',
            'type' => 'nullable|in:buy,sell,transfer,refund',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $query = Transaction::with('token:id,symbol,name')
            ->where('user_id', $user->id)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $items = $query->limit($request->get('limit', 50))->get()
            ->map(function (Transaction $t) {
                return [
                    'transaction_id' => $t->transaction_id,
                    'status' => $t->status,
                    'type' => $t->type,
                    'token_symbol' => $t->token?->symbol,
                    'amount' => $t->amount,
                    'price' => $t->price,
                    'total_amount' => $t->total_amount,
                    'created_at' => $t->created_at,
                    'processed_at' => $t->processed_at,
                ];
            });

        return response()->json([
            'transactions' => $items,
            'count' => $items->count(),
        ]);
    }
}


