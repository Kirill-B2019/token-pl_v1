<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\Token;
use App\Models\Transaction;
use App\Models\UserBalance;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BrokerController extends Controller
{
    /**
     * Show broker dashboard
     */
    public function dashboard()
    {
        $broker = Broker::where('user_id', Auth::id())->first();
        
        if (!$broker) {
            return redirect()->route('broker.setup');
        }
        
        // Get pending transactions
        $pendingTransactions = Transaction::with(['user', 'token'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);
        
        // Get token reserves
        $tokens = Token::with('userBalances')->get();
        
        // Get low reserve alerts
        $lowReserveTokens = $tokens->filter(function ($token) {
            return $token->available_supply < 100; // Threshold for low reserve
        });
        
        return view('broker.dashboard', compact('broker', 'pendingTransactions', 'tokens', 'lowReserveTokens'));
    }

    /**
     * Show broker setup
     */
    public function setup()
    {
        return view('broker.setup');
    }

    /**
     * Store broker configuration
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
            'exchange_url' => 'required|url',
            'min_reserve_threshold' => 'required|numeric|min:0',
        ]);

        $broker = Broker::create([
            'name' => $request->name,
            'api_key' => $request->api_key,
            'api_secret' => $request->api_secret,
            'exchange_url' => $request->exchange_url,
            'min_reserve_threshold' => $request->min_reserve_threshold,
            'user_id' => Auth::id(),
        ]);

        // Log audit
        AuditLog::createLog(
            'broker_created',
            'Broker',
            $broker->id,
            Auth::id(),
            null,
            $broker->toArray()
        );

        return redirect()->route('broker.dashboard')
            ->with('success', 'Конфигурация брокера сохранена успешно.');
    }

    /**
     * Process pending transactions
     */
    public function processTransaction(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Транзакция уже обработана.');
        }

        DB::beginTransaction();
        
        try {
            if ($transaction->type === 'buy') {
                $this->processBuyTransaction($transaction);
            } elseif ($transaction->type === 'sell') {
                $this->processSellTransaction($transaction);
            }

            // Log audit
            AuditLog::createLog(
                'transaction_processed',
                'Transaction',
                $transaction->id,
                Auth::id(),
                ['status' => 'pending'],
                ['status' => 'completed']
            );

            DB::commit();
            
            return back()->with('success', 'Транзакция обработана успешно.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $transaction->markAsFailed();
            
            return back()->with('error', 'Ошибка при обработке транзакции: ' . $e->getMessage());
        }
    }

    /**
     * Process buy transaction
     */
    private function processBuyTransaction(Transaction $transaction)
    {
        $token = $transaction->token;
        
        // Check if we have enough tokens in reserve
        if (!$token->hasEnoughSupply($transaction->amount)) {
            // Try to buy from exchange
            $this->buyFromExchange($token, $transaction->amount);
        }
        
        // Transfer tokens to user
        $token->reduceSupply($transaction->amount);
        
        // Update user balance
        $userBalance = UserBalance::firstOrCreate([
            'user_id' => $transaction->user_id,
            'token_id' => $token->id,
        ]);
        
        $userBalance->addBalance($transaction->amount);
        
        // Mark transaction as completed
        $transaction->markAsCompleted();
    }

    /**
     * Process sell transaction
     */
    private function processSellTransaction(Transaction $transaction)
    {
        $token = $transaction->token;
        $userBalance = UserBalance::where('user_id', $transaction->user_id)
            ->where('token_id', $token->id)
            ->first();
        
        if (!$userBalance || !$userBalance->hasEnoughBalance($transaction->amount)) {
            throw new \Exception('Недостаточно токенов для продажи.');
        }
        
        // Subtract from user balance
        $userBalance->subtractBalance($transaction->amount);
        
        // Add to token supply
        $token->increaseSupply($transaction->amount);
        
        // Unlock the balance
        $userBalance->unlockBalance($transaction->amount);
        
        // Mark transaction as completed
        $transaction->markAsCompleted();
    }

    /**
     * Buy tokens from exchange
     */
    private function buyFromExchange(Token $token, float $amount)
    {
        // This would integrate with actual exchange API
        // For now, we'll just increase the supply
        $token->increaseSupply($amount);
        
        // Log the exchange purchase
        AuditLog::createLog(
            'exchange_purchase',
            'Token',
            $token->id,
            Auth::id(),
            null,
            ['amount' => $amount, 'source' => 'exchange']
        );
    }

    /**
     * Show token management
     */
    public function tokens()
    {
        $tokens = Token::with('userBalances')->get();
        
        return view('broker.tokens', compact('tokens'));
    }

    /**
     * Update token price
     */
    public function updateTokenPrice(Request $request, Token $token)
    {
        $request->validate([
            'current_price' => 'required|numeric|min:0',
        ]);

        $oldPrice = $token->current_price;
        $token->update(['current_price' => $request->current_price]);
        
        // Log audit
        AuditLog::createLog(
            'token_price_updated',
            'Token',
            $token->id,
            Auth::id(),
            ['current_price' => $oldPrice],
            ['current_price' => $request->current_price]
        );

        return back()->with('success', 'Цена токена обновлена успешно.');
    }

    /**
     * Show reserve management
     */
    public function reserves()
    {
        $broker = Broker::where('user_id', Auth::id())->first();
        $tokens = Token::active()->get();
        
        return view('broker.reserves', compact('broker', 'tokens'));
    }

    /**
     * Add to token reserve
     */
    public function addReserve(Request $request, Token $token)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.00000001',
        ]);

        $token->increaseSupply($request->amount);
        
        // Log audit
        AuditLog::createLog(
            'reserve_added',
            'Token',
            $token->id,
            Auth::id(),
            null,
            ['amount' => $request->amount]
        );

        return back()->with('success', 'Резерв токенов пополнен успешно.');
    }
}
