<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\TokenPackage;
use App\Models\Transaction;
use App\Models\UserBalance;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Show client dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get user balances
        $balances = UserBalance::with('token')
            ->where('user_id', $user->id)
            ->get();
        
        // Get recent transactions
        $recentTransactions = Transaction::with('token')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();
        
        // Get available token packages
        $packages = TokenPackage::active()->ordered()->get();
        
        return view('client.dashboard', compact('balances', 'recentTransactions', 'packages'));
    }

    /**
     * Show token packages
     */
    public function packages()
    {
        $packages = TokenPackage::active()->ordered()->get();
        $tokens = Token::active()->get();
        
        return view('client.packages', compact('packages', 'tokens'));
    }

    /**
     * Show purchase form
     */
    public function showPurchase(TokenPackage $package)
    {
        return view('client.purchase', compact('package'));
    }

    /**
     * Process token purchase
     */
    public function purchase(Request $request, TokenPackage $package)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'card_number' => 'required|string',
            'expiry_date' => 'required|string',
            'cvv' => 'required|string',
            'cardholder_name' => 'required|string',
            'bank_code' => 'nullable|string|in:MTS,SBER,VTB,ALFA',
        ]);

        DB::beginTransaction();
        
        try {
            // Generate unique transaction ID
            $transactionId = 'TXN_' . time() . '_' . rand(1000, 9999);
            
            // Create transaction
            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'token_id' => $package->token_id ?? 1, // Default token ID
                'type' => 'buy',
                'status' => 'pending',
                'amount' => $package->token_amount,
                'price' => $package->final_price,
                'total_amount' => $package->final_price,
                'payment_method' => $request->payment_method,
                'payment_reference' => $transactionId,
                'metadata' => [
                    'package_id' => $package->id,
                    'card_last_four' => substr($request->card_number, -4),
                    'cardholder_name' => $request->cardholder_name,
                    'bank_code' => $request->bank_code ?? 'MTS',
                ],
            ]);

            // Process payment through selected bank
            $paymentResult = $this->processPaymentThroughBank($request, $transaction, $package);

            if ($paymentResult['success']) {
                // Log audit
                AuditLog::createLog(
                    'token_purchase_initiated',
                    'Transaction',
                    $transaction->id,
                    Auth::id(),
                    null,
                    $transaction->toArray()
                );

                DB::commit();
                
                return redirect()->route('client.transactions.show', $transaction)
                    ->with('success', 'Покупка токенов инициирована. Ожидайте подтверждения.');
            } else {
                DB::rollBack();
                
                return back()->with('error', 'Ошибка при обработке платежа: ' . $paymentResult['error']);
            }
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Ошибка при обработке покупки: ' . $e->getMessage());
        }
    }

    /**
     * Process payment through selected bank
     */
    private function processPaymentThroughBank(Request $request, Transaction $transaction, TokenPackage $package): array
    {
        $bankCode = $request->bank_code ?? 'MTS';
        
        // Get bank configuration
        $bank = \App\Models\Bank::where('code', $bankCode)->first();
        if (!$bank || !$bank->is_active) {
            return [
                'success' => false,
                'error' => 'Выбранный банк недоступен'
            ];
        }

        // Prepare payment data
        $paymentData = [
            'merchant_id' => $bank->merchant_id,
            'api_key' => $bank->api_key,
            'transaction_id' => $transaction->payment_reference,
            'amount' => $package->final_price,
            'currency' => 'RUB',
            'card_number' => $request->card_number,
            'expiry_date' => $request->expiry_date,
            'cvv' => $request->cvv,
            'cardholder_name' => $request->cardholder_name,
            'description' => "Покупка токенов {$package->name}",
        ];

        // Choose API endpoint based on bank
        $apiEndpoint = match($bankCode) {
            'MTS' => '/api/mts/payment',
            'SBER' => '/api/bank/payment',
            'VTB' => '/api/bank/payment',
            'ALFA' => '/api/bank/payment',
            default => '/api/bank/payment'
        };

        try {
            // Make internal API call to process payment
            $response = \Illuminate\Support\Facades\Http::post(
                url($apiEndpoint),
                $paymentData
            );

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Update transaction with bank response
                $transaction->update([
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'bank_response' => $responseData,
                        'bank_code' => $bankCode,
                    ])
                ]);

                return [
                    'success' => true,
                    'data' => $responseData
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->json()['error'] ?? 'Ошибка обработки платежа'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Ошибка соединения с банком: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Show sell form
     */
    public function showSell()
    {
        $user = Auth::user();
        $balances = UserBalance::with('token')
            ->where('user_id', $user->id)
            ->where('balance', '>', 0)
            ->get();
        
        return view('client.sell', compact('balances'));
    }

    /**
     * Process token sale
     */
    public function sell(Request $request)
    {
        $request->validate([
            'token_id' => 'required|exists:tokens,id',
            'amount' => 'required|numeric|min:0.00000001',
        ]);

        $user = Auth::user();
        $token = Token::findOrFail($request->token_id);
        
        // Check if user has enough balance
        $balance = UserBalance::where('user_id', $user->id)
            ->where('token_id', $token->id)
            ->first();
            
        if (!$balance || $balance->available_balance < $request->amount) {
            return back()->with('error', 'Недостаточно токенов для продажи.');
        }

        DB::beginTransaction();
        
        try {
            // Create transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'token_id' => $token->id,
                'type' => 'sell',
                'status' => 'pending',
                'amount' => $request->amount,
                'price' => $token->current_price,
                'total_amount' => $request->amount * $token->current_price,
                'metadata' => [
                    'market_price' => $token->current_price,
                ],
            ]);

            // Lock the balance
            $balance->lockBalance($request->amount);

            // Log audit
            AuditLog::createLog(
                'token_sale_initiated',
                'Transaction',
                $transaction->id,
                $user->id,
                null,
                $transaction->toArray()
            );

            DB::commit();
            
            return redirect()->route('client.transactions.show', $transaction)
                ->with('success', 'Заявка на продажу токенов создана. Ожидайте обработки.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Ошибка при создании заявки на продажу: ' . $e->getMessage());
        }
    }

    /**
     * Show transaction details
     */
    public function showTransaction(Transaction $transaction)
    {
        // Ensure user can only view their own transactions
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }
        
        return view('client.transaction', compact('transaction'));
    }

    /**
     * Show transaction history
     */
    public function transactions()
    {
        $user = Auth::user();
        $transactions = Transaction::with('token')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);
        
        return view('client.transactions', compact('transactions'));
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        $user = Auth::user();
        return view('client.profile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        $oldValues = $user->toArray();
        
        $user->update($request->only(['name', 'phone']));
        
        // Log audit
        AuditLog::createLog(
            'profile_updated',
            'User',
            $user->id,
            $user->id,
            $oldValues,
            $user->toArray()
        );

        return back()->with('success', 'Профиль обновлен успешно.');
    }

    /**
<<<<<<< Current (Your changes)
=======
     * Show balance top-up form
     */
    public function showTopUp()
    {
        return view('client.balance.topup');
    }

    /**
>>>>>>> Incoming (Background Agent changes)
     * Process balance top-up
     */
    public function topUpBalance(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:1000000',
            'bank_code' => 'required|string|in:MTS,SBER,VTB,ALFA',
        ]);

        $user = Auth::user();
        $amount = $request->amount;
        $bankCode = $request->bank_code;

        // Get bank
        $bank = \App\Models\Bank::where('code', $bankCode)
            ->where('is_active', true)
            ->first();

        if (!$bank) {
            return back()->with('error', 'Выбранный банк недоступен.');
        }

        // Calculate commission
        $commission = $bank->calculateCommission($amount);
        $finalAmount = $amount - $commission;

        DB::beginTransaction();

        try {
            // Generate unique transaction ID
            $transactionId = 'TXN_' . time() . '_' . rand(1000, 9999);

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'token_id' => null, // Balance top-up doesn't require token
                'type' => 'deposit', // New transaction type for balance top-up
                'status' => 'pending',
                'amount' => $finalAmount, // Amount after commission
                'price' => 1, // 1 RUB = 1 RUB
                'total_amount' => $amount, // Total amount including commission
                'fee' => $commission,
                'payment_method' => 'bank_card',
                'payment_reference' => $transactionId,
                'metadata' => [
                    'bank_code' => $bankCode,
                    'bank_name' => $bank->name,
                    'original_amount' => $amount,
                    'commission' => $commission,
                    'final_amount' => $finalAmount,
                    'transaction_type' => 'balance_topup',
                ],
            ]);

            // Process payment through bank API
            $paymentData = [
                'merchant_id' => $bank->merchant_id,
                'api_key' => $bank->api_key,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'currency' => 'RUB',
                'description' => 'Пополнение баланса на сумму ' . $amount . ' ₽',
            ];

            // Determine API endpoint based on bank
            $apiEndpoint = match($bankCode) {
                'MTS' => '/api/mts/payment',
                default => '/api/bank/payment'
            };

            // Make API call to process payment
            $response = \Illuminate\Support\Facades\Http::post(
                url($apiEndpoint),
                $paymentData
            );

            if ($response->successful()) {
                $responseData = $response->json();

                // Update transaction
                $transaction->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'bank_response' => $responseData,
                        'bank_transaction_id' => $responseData['bank_transaction_id'] ?? null,
                    ])
                ]);

                // Log audit
                AuditLog::createLog(
                    'balance_topup_completed',
                    'Transaction',
                    $transaction->id,
                    $user->id,
                    null,
                    $transaction->toArray()
                );

                DB::commit();

                return redirect()->route('client.payment.success', ['transaction_id' => $transactionId])
                    ->with('success', 'Баланс успешно пополнен на сумму ' . number_format($finalAmount, 2, '.', ' ') . ' ₽');
            } else {
                $error = $response->json()['error'] ?? 'Ошибка обработки платежа';
                
                $transaction->update([
                    'status' => 'failed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'error' => $error,
                    ])
                ]);

                DB::rollBack();

                return redirect()->route('client.payment.fail', [
                    'transaction_id' => $transactionId,
                    'error' => $error
                ])->with('error', 'Ошибка при пополнении баланса: ' . $error);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($transaction)) {
                $transaction->markAsFailed();
            }

            return back()->with('error', 'Ошибка при пополнении баланса: ' . $e->getMessage());
        }
    }

    /**
     * Show payment success page
     */
    public function paymentSuccess(Request $request)
    {
        $transactionId = $request->get('transaction_id');
        $transaction = null;
        
        if ($transactionId) {
            $transaction = Transaction::where('payment_reference', $transactionId)
                ->where('user_id', Auth::id())
                ->first();
        }
        
        return view('client.payment.success', compact('transaction'));
    }

    /**
     * Show payment fail page
     */
    public function paymentFail(Request $request)
    {
        $transactionId = $request->get('transaction_id');
        $error = $request->get('error', 'Неизвестная ошибка');
        $transaction = null;
        
        if ($transactionId) {
            $transaction = Transaction::where('payment_reference', $transactionId)
                ->where('user_id', Auth::id())
                ->first();
        }
        
        return view('client.payment.fail', compact('transaction', 'error'));
    }

    /**
     * Show attach card form
     */
    public function showAttachCard()
    {
        return view('client.cards.attach');
    }

    /**
     * Process card attachment
     */
    public function attachCard(Request $request)
    {
        // TODO: Implement card attachment logic
        return back()->with('success', 'Карта успешно привязана.');
    }
}
