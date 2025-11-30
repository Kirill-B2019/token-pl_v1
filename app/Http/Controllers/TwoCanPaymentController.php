<?php
// |KB Контроллер для обработки платежей через 2can

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TwoCanPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TwoCanPaymentController extends Controller
{
    protected TwoCanPaymentService $paymentService;

    public function __construct(TwoCanPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Отображение формы пополнения баланса
     */
    public function showTopUpForm(): View
    {
        $user = auth()->user();

        return view('client.balance.topup', [
            'user' => $user,
            'minAmount' => config('twocan.min_amount'),
            'maxAmount' => config('twocan.max_amount'),
        ]);
    }

    /**
     * Создание платежа и перенаправление на 2can
     */
    public function createPayment(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:' . config('twocan.min_amount'),
                'max:' . config('twocan.max_amount'),
            ],
            'card_token' => 'nullable|string',
        ]);

        $user = auth()->user();
        $amount = (float) $request->input('amount');
        $cardToken = $request->input('card_token');

        try {
            $result = $this->paymentService->createPayment($user, $amount, 'Пополнение баланса через 2can', $cardToken);

            if ($result['success'] && isset($result['payment_url'])) {
                return redirect($result['payment_url']);
            } else {
                return back()->with('error', $result['error'] ?? 'Не удалось создать платеж');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Произошла ошибка при создании платежа');
        }
    }

    /**
     * Обработка успешного платежа (перенаправление пользователя)
     */
    public function paymentSuccess(Request $request): View
    {
        $paymentId = $request->query('payment_id');
        $transaction = null;

        if ($paymentId) {
            $transaction = \App\Models\Transaction::where('payment_id', $paymentId)
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('client.payment.success', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Обработка неудачного платежа (перенаправление пользователя)
     */
    public function paymentFail(Request $request): View
    {
        $paymentId = $request->query('payment_id');
        $transaction = null;
        $error = $request->query('error', 'Платеж не был завершен');

        if ($paymentId) {
            $transaction = \App\Models\Transaction::where('payment_id', $paymentId)
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('client.payment.fail', [
            'transaction' => $transaction,
            'error' => $error,
        ]);
    }

    /**
     * Webhook для обработки callback от 2can
     */
    public function webhook(Request $request)
    {
        try {
            $result = $this->paymentService->processPaymentSuccess($request);

            if ($result) {
                return response()->json(['status' => 'success']);
            } else {
                return response()->json(['status' => 'error'], 400);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('2can webhook error', [
                'exception' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }
}