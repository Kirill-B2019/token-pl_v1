<?php

namespace App\Http\Controllers;

use App\Models\TronWallet;
use App\Services\TronWalletService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TronWalletController extends Controller
{
    private TronWalletService $tronWalletService;

    public function __construct(TronWalletService $tronWalletService)
    {
        $this->tronWalletService = $tronWalletService;
    }

    /**
     * Create new TRON wallet for authenticated user
     */
    public function createWallet(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Check if user already has a wallet
        $existingWallet = TronWallet::where('user_id', $user->id)->first();
        if ($existingWallet) {
            return response()->json([
                'success' => false,
                'error' => 'У вас уже есть кошелек TRON',
                'wallet' => $existingWallet->only(['address', 'balance_trx', 'balance_usdt']),
            ], 400);
        }

        try {
            $wallet = $this->tronWalletService->generateWallet($user);
            
            return response()->json([
                'success' => true,
                'message' => 'Кошелек TRON успешно создан',
                'wallet' => [
                    'id' => $wallet->id,
                    'address' => $wallet->address,
                    'balance_trx' => $wallet->balance_trx,
                    'balance_usdt' => $wallet->balance_usdt,
                    'is_active' => $wallet->is_active,
                    'created_at' => $wallet->created_at,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's TRON wallet
     */
    public function getWallet(): JsonResponse
    {
        $user = Auth::user();
        $wallet = TronWallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'error' => 'Кошелек TRON не найден',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'wallet' => [
                'id' => $wallet->id,
                'address' => $wallet->address,
                'balance_trx' => $wallet->balance_trx,
                'balance_usdt' => $wallet->balance_usdt,
                'total_balance_usd' => $wallet->total_balance_usd,
                'is_active' => $wallet->is_active,
                'last_sync_at' => $wallet->last_sync_at,
                'created_at' => $wallet->created_at,
            ],
        ]);
    }

    /**
     * Sync wallet balance
     */
    public function syncBalance(): JsonResponse
    {
        $user = Auth::user();
        $wallet = TronWallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'error' => 'Кошелек TRON не найден',
            ], 404);
        }

        $success = $this->tronWalletService->syncWalletBalance($wallet);
        
        if ($success) {
            $wallet->refresh();
            return response()->json([
                'success' => true,
                'message' => 'Баланс успешно обновлен',
                'balance' => [
                    'trx' => $wallet->balance_trx,
                    'usdt' => $wallet->balance_usdt,
                    'total_usd' => $wallet->total_balance_usd,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Не удалось обновить баланс',
        ], 500);
    }

    /**
     * Send TRX transaction
     */
    public function sendTrx(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'to_address' => 'required|string|size:34',
            'amount' => 'required|numeric|min:0.000001',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Неверные данные',
                'details' => $validator->errors(),
            ], 400);
        }

        $user = Auth::user();
        $wallet = TronWallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'error' => 'Кошелек TRON не найден',
            ], 404);
        }

        $result = $this->tronWalletService->sendTrx(
            $wallet,
            $request->to_address,
            $request->amount
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Send USDT transaction
     */
    public function sendUsdt(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'to_address' => 'required|string|size:34',
            'amount' => 'required|numeric|min:0.000001',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Неверные данные',
                'details' => $validator->errors(),
            ], 400);
        }

        $user = Auth::user();
        $wallet = TronWallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'error' => 'Кошелек TRON не найден',
            ], 404);
        }

        $result = $this->tronWalletService->sendUsdt(
            $wallet,
            $request->to_address,
            $request->amount
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Неверные параметры',
                'details' => $validator->errors(),
            ], 400);
        }

        $user = Auth::user();
        $wallet = TronWallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'error' => 'Кошелек TRON не найден',
            ], 404);
        }

        $limit = $request->get('limit', 50);
        $transactions = $this->tronWalletService->getTransactionHistory($wallet, $limit);

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
            'wallet_address' => $wallet->address,
        ]);
    }

    /**
     * Get wallet QR code data
     */
    public function getQrCode(): JsonResponse
    {
        $user = Auth::user();
        $wallet = TronWallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'error' => 'Кошелек TRON не найден',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'qr_data' => $wallet->getQrCodeData(),
        ]);
    }

    /**
     * Validate TRON address
     */
    public function validateAddress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Адрес не указан',
            ], 400);
        }

        $isValid = TronWallet::isValidAddress($request->address);

        return response()->json([
            'success' => true,
            'is_valid' => $isValid,
            'address' => $request->address,
        ]);
    }

    // Web Interface Methods

    /**
     * Show wallet dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $wallet = TronWallet::where('user_id', $user->id)->first();

        return view('client.wallet.index', compact('wallet'));
    }

    /**
     * Show send transaction form
     */
    public function sendForm()
    {
        $user = Auth::user();
        $wallet = TronWallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return redirect()->route('client.wallet')->with('error', 'Сначала создайте кошелек TRON');
        }

        return view('client.wallet.send', compact('wallet'));
    }

    /**
     * Process send transaction
     */
    public function sendTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to_address' => 'required|string|size:34',
            'amount' => 'required|numeric|min:0.000001',
            'currency' => 'required|in:TRX,USDT',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $wallet = TronWallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return redirect()->route('client.wallet')->with('error', 'Кошелек TRON не найден');
        }

        $result = $request->currency === 'TRX' 
            ? $this->tronWalletService->sendTrx($wallet, $request->to_address, $request->amount)
            : $this->tronWalletService->sendUsdt($wallet, $request->to_address, $request->amount);

        if ($result['success']) {
            return redirect()->route('client.wallet.history')
                ->with('success', 'Транзакция успешно отправлена. ID: ' . $result['transaction_id']);
        }

        return back()->with('error', $result['error'])->withInput();
    }

    /**
     * Show transaction history
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $wallet = TronWallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return redirect()->route('client.wallet')->with('error', 'Сначала создайте кошелек TRON');
        }

        $limit = $request->get('limit', 50);
        $transactions = $this->tronWalletService->getTransactionHistory($wallet, $limit);

        return view('client.wallet.history', compact('wallet', 'transactions'));
    }
}
