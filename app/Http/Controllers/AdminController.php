<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Token;
use App\Models\Transaction;
use App\Models\WinnerLoser;
use App\Models\AuditLog;
use App\Models\Broker;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_transactions' => Transaction::count(),
            'pending_transactions' => Transaction::where('status', 'pending')->count(),
            'completed_transactions' => Transaction::where('status', 'completed')->count(),
            'total_tokens' => Token::count(),
            'active_tokens' => Token::where('is_active', true)->count(),
        ];

        // Get recent activity
        $recentTransactions = Transaction::with(['user', 'token'])
            ->latest()
            ->limit(10)
            ->get();

        $recentAuditLogs = AuditLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentTransactions', 'recentAuditLogs'));
    }

    /**
     * Show users management
     */
    public function users()
    {
        $users = User::with('transactions')
            ->latest()
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * Show user details
     */
    public function showUser(User $user)
    {
        $user->load(['transactions.token', 'balances.token', 'winnerLosers.token', 'userGroups']);
        
        return view('admin.user', compact('user'));
    }

    /**
     * Update user status
     */
    public function updateUserStatus(Request $request, User $user)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $oldStatus = $user->is_active;
        $user->update(['is_active' => $request->is_active]);

        // Log audit
        AuditLog::createLog(
            'user_status_updated',
            'User',
            $user->id,
            Auth::id(),
            ['is_active' => $oldStatus],
            ['is_active' => $request->is_active]
        );

        return back()->with('success', 'Статус пользователя обновлен успешно.');
    }

    /**
     * Show transactions management
     */
    public function transactions()
    {
        $transactions = Transaction::with(['user', 'token'])
            ->latest()
            ->paginate(20);

        return view('admin.transactions', compact('transactions'));
    }

    /**
     * Show transaction details
     */
    public function showTransaction(Transaction $transaction)
    {
        $transaction->load(['user', 'token']);
        
        return view('admin.transaction', compact('transaction'));
    }

    /**
     * Cancel transaction
     */
    public function cancelTransaction(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Транзакцию можно отменить только в статусе "Ожидает".');
        }

        $transaction->markAsCancelled();

        // Unlock user balance if it was locked
        if ($transaction->type === 'sell') {
            $userBalance = \App\Models\UserBalance::where('user_id', $transaction->user_id)
                ->where('token_id', $transaction->token_id)
                ->first();
            
            if ($userBalance) {
                $userBalance->unlockBalance($transaction->amount);
            }
        }

        // Log audit
        AuditLog::createLog(
            'transaction_cancelled',
            'Transaction',
            $transaction->id,
            Auth::id(),
            ['status' => 'pending'],
            ['status' => 'cancelled']
        );

        return back()->with('success', 'Транзакция отменена успешно.');
    }

    /**
     * Show tokens management
     */
    public function tokens()
    {
        $tokens = Token::with('userBalances')->get();

        return view('admin.tokens', compact('tokens'));
    }

    /**
     * Create new token
     */
    public function createToken()
    {
        return view('admin.token.create');
    }

    /**
     * Store new token
     */
    public function storeToken(Request $request)
    {
        $request->validate([
            'symbol' => 'required|string|max:10|unique:tokens',
            'name' => 'required|string|max:255',
            'current_price' => 'required|numeric|min:0',
            'total_supply' => 'required|numeric|min:0',
            'available_supply' => 'required|numeric|min:0',
        ]);

        $token = Token::create($request->all());

        // Log audit
        AuditLog::createLog(
            'token_created',
            'Token',
            $token->id,
            Auth::id(),
            null,
            $token->toArray()
        );

        return redirect()->route('admin.tokens')
            ->with('success', 'Токен создан успешно.');
    }

    /**
     * Show winners/losers management
     */
    public function winnersLosers()
    {
        $winnersLosers = WinnerLoser::with(['user', 'token'])
            ->latest()
            ->paginate(20);

        return view('admin.winners-losers', compact('winnersLosers'));
    }

    /**
     * Create winners/losers list
     */
    public function createWinnersLosers()
    {
        return view('admin.create-winners-losers');
    }

    /**
     * Store winners/losers list
     */
    public function storeWinnersLosers(Request $request)
    {
        $request->validate([
            'winners' => 'required|array',
            'winners.*.user_id' => 'required|exists:users,id',
            'winners.*.amount' => 'required|numeric|min:0',
            'winners.*.token_id' => 'required|exists:tokens,id',
            'losers' => 'required|array',
            'losers.*.user_id' => 'required|exists:users,id',
            'losers.*.amount' => 'required|numeric|min:0',
            'losers.*.token_id' => 'required|exists:tokens,id',
        ]);

        DB::beginTransaction();

        try {
            // Create winner records
            foreach ($request->winners as $winner) {
                WinnerLoser::create([
                    'user_id' => $winner['user_id'],
                    'type' => 'winner',
                    'amount' => $winner['amount'],
                    'token_amount' => $winner['amount'],
                    'token_id' => $winner['token_id'],
                    'status' => 'pending',
                ]);
            }

            // Create loser records
            foreach ($request->losers as $loser) {
                WinnerLoser::create([
                    'user_id' => $loser['user_id'],
                    'type' => 'loser',
                    'amount' => $loser['amount'],
                    'token_amount' => $loser['amount'],
                    'token_id' => $loser['token_id'],
                    'status' => 'pending',
                ]);
            }

            // Log audit
            AuditLog::createLog(
                'winners_losers_created',
                'WinnerLoser',
                0,
                Auth::id(),
                null,
                [
                    'winners_count' => count($request->winners),
                    'losers_count' => count($request->losers),
                ]
            );

            DB::commit();

            return redirect()->route('admin.winners-losers')
                ->with('success', 'Список победителей и проигравших создан успешно.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Ошибка при создании списка: ' . $e->getMessage());
        }
    }

    /**
     * Process winner/loser payment
     */
    public function processPayment(WinnerLoser $winnerLoser)
    {
        if ($winnerLoser->status !== 'pending') {
            return back()->with('error', 'Выплата уже обработана.');
        }

        $winnerLoser->markAsProcessed();

        // Log audit
        AuditLog::createLog(
            'payment_processed',
            'WinnerLoser',
            $winnerLoser->id,
            Auth::id(),
            ['status' => 'pending'],
            ['status' => 'processed']
        );

        return back()->with('success', 'Выплата обработана успешно.');
    }

    /**
     * Show audit logs
     */
    public function auditLogs()
    {
        $auditLogs = AuditLog::with('user')
            ->latest()
            ->paginate(50);

        return view('admin.audit-logs', compact('auditLogs'));
    }

    /**
     * Show brokers management
     */
    public function brokers()
    {
        $brokers = Broker::with('user')->get();

        return view('admin.brokers', compact('brokers'));
    }

    /**
     * Show banks management
     */
    public function banks()
    {
        $banks = Bank::all();

        return view('admin.banks', compact('banks'));
    }

    /**
     * Show system settings
     */
    public function settings()
    {
        return view('admin.settings');
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'system_name' => 'required|string|max:255',
            'maintenance_mode' => 'boolean',
            'registration_enabled' => 'boolean',
        ]);

        // Update settings in config or database
        // This would typically be stored in a settings table

        // Log audit
        AuditLog::createLog(
            'settings_updated',
            'System',
            0,
            Auth::id(),
            null,
            $request->all()
        );

        return back()->with('success', 'Настройки системы обновлены успешно.');
    }
}
