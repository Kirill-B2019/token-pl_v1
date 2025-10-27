<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BrokerApiController;
use App\Http\Controllers\Api\BankApiController;
use App\Http\Controllers\Api\ExchangeApiController;
use App\Http\Controllers\TronWalletController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Broker API routes
Route::prefix('broker')->group(function () {
    Route::get('/tokens', [BrokerApiController::class, 'getTokens']);
    Route::get('/token-balance', [BrokerApiController::class, 'getTokenBalance']);
    Route::post('/transfer', [BrokerApiController::class, 'transferTokens']);
    Route::get('/transaction-status', [BrokerApiController::class, 'getTransactionStatus']);
    Route::post('/update-price', [BrokerApiController::class, 'updateTokenPrice']);
});

// Bank API routes
Route::prefix('bank')->group(function () {
    Route::post('/payment', [BankApiController::class, 'processPayment']);
    Route::post('/refund', [BankApiController::class, 'processRefund']);
    Route::get('/verify', [BankApiController::class, 'verifyTransaction']);
    Route::get('/history', [BankApiController::class, 'getTransactionHistory']);
});

// Exchange API routes
Route::prefix('exchange')->group(function () {
    Route::get('/price', [ExchangeApiController::class, 'getTokenPrice']);
    Route::post('/buy', [ExchangeApiController::class, 'placeBuyOrder']);
    Route::post('/sell', [ExchangeApiController::class, 'placeSellOrder']);
    Route::get('/order-status', [ExchangeApiController::class, 'getOrderStatus']);
    Route::post('/cancel-order', [ExchangeApiController::class, 'cancelOrder']);
});

// MTS Bank API routes
Route::prefix('mts')->group(function () {
    Route::post('/payment', [App\Http\Controllers\Api\MtsApiController::class, 'processPayment']);
    Route::post('/refund', [App\Http\Controllers\Api\MtsApiController::class, 'processRefund']);
    Route::get('/status', [App\Http\Controllers\Api\MtsApiController::class, 'getTransactionStatus']);
    Route::post('/webhook', [App\Http\Controllers\Api\MtsApiController::class, 'handleWebhook']);
});

// TRON Wallet API routes
Route::middleware('auth:sanctum')->prefix('tron')->group(function () {
    Route::post('/wallet/create', [TronWalletController::class, 'createWallet']);
    Route::get('/wallet', [TronWalletController::class, 'getWallet']);
    Route::post('/wallet/sync', [TronWalletController::class, 'syncBalance']);
    Route::post('/wallet/send-trx', [TronWalletController::class, 'sendTrx']);
    Route::post('/wallet/send-usdt', [TronWalletController::class, 'sendUsdt']);
    Route::get('/wallet/transactions', [TronWalletController::class, 'getTransactionHistory']);
    Route::get('/wallet/qr-code', [TronWalletController::class, 'getQrCode']);
    Route::post('/validate-address', [TronWalletController::class, 'validateAddress']);
});
