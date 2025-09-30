<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

// Client routes
Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\ClientController::class, 'dashboard'])->name('dashboard');
    Route::get('/packages', [App\Http\Controllers\ClientController::class, 'packages'])->name('packages');
    Route::get('/packages/{package}/purchase', [App\Http\Controllers\ClientController::class, 'showPurchase'])->name('packages.purchase');
    Route::post('/packages/{package}/purchase', [App\Http\Controllers\ClientController::class, 'purchase'])->name('packages.purchase.store');
    Route::get('/sell', [App\Http\Controllers\ClientController::class, 'showSell'])->name('sell');
    Route::post('/sell', [App\Http\Controllers\ClientController::class, 'sell'])->name('sell.store');
    Route::get('/transactions', [App\Http\Controllers\ClientController::class, 'transactions'])->name('transactions');
    Route::get('/transactions/{transaction}', [App\Http\Controllers\ClientController::class, 'showTransaction'])->name('transactions.show');
    Route::get('/profile', [App\Http\Controllers\ClientController::class, 'profile'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\ClientController::class, 'updateProfile'])->name('profile.update');
});

// Broker routes
Route::middleware(['auth', 'role:broker'])->prefix('broker')->name('broker.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\BrokerController::class, 'dashboard'])->name('dashboard');
    Route::get('/setup', [App\Http\Controllers\BrokerController::class, 'setup'])->name('setup');
    Route::post('/setup', [App\Http\Controllers\BrokerController::class, 'store'])->name('setup.store');
    Route::post('/transactions/{transaction}/process', [App\Http\Controllers\BrokerController::class, 'processTransaction'])->name('transactions.process');
    Route::get('/tokens', [App\Http\Controllers\BrokerController::class, 'tokens'])->name('tokens');
    Route::post('/tokens/{token}/price', [App\Http\Controllers\BrokerController::class, 'updateTokenPrice'])->name('tokens.price.update');
    Route::get('/reserves', [App\Http\Controllers\BrokerController::class, 'reserves'])->name('reserves');
    Route::post('/reserves/{token}/add', [App\Http\Controllers\BrokerController::class, 'addReserve'])->name('reserves.add');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [App\Http\Controllers\AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}', [App\Http\Controllers\AdminController::class, 'showUser'])->name('users.show');
    Route::post('/users/{user}/status', [App\Http\Controllers\AdminController::class, 'updateUserStatus'])->name('users.status.update');
    Route::get('/transactions', [App\Http\Controllers\AdminController::class, 'transactions'])->name('transactions');
    Route::get('/transactions/{transaction}', [App\Http\Controllers\AdminController::class, 'showTransaction'])->name('transactions.show');
    Route::post('/transactions/{transaction}/cancel', [App\Http\Controllers\AdminController::class, 'cancelTransaction'])->name('transactions.cancel');
    Route::get('/tokens', [App\Http\Controllers\AdminController::class, 'tokens'])->name('tokens');
    Route::get('/tokens/create', [App\Http\Controllers\AdminController::class, 'createToken'])->name('tokens.create');
    Route::post('/tokens', [App\Http\Controllers\AdminController::class, 'storeToken'])->name('tokens.store');
    Route::get('/winners-losers', [App\Http\Controllers\AdminController::class, 'winnersLosers'])->name('winners-losers');
    Route::get('/winners-losers/create', [App\Http\Controllers\AdminController::class, 'createWinnersLosers'])->name('winners-losers.create');
    Route::post('/winners-losers', [App\Http\Controllers\AdminController::class, 'storeWinnersLosers'])->name('winners-losers.store');
    Route::post('/winners-losers/{winnerLoser}/process', [App\Http\Controllers\AdminController::class, 'processPayment'])->name('winners-losers.process');
    Route::get('/audit-logs', [App\Http\Controllers\AdminController::class, 'auditLogs'])->name('audit-logs');
    Route::get('/brokers', [App\Http\Controllers\AdminController::class, 'brokers'])->name('brokers');
    Route::get('/banks', [App\Http\Controllers\AdminController::class, 'banks'])->name('banks');
    Route::get('/settings', [App\Http\Controllers\AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [App\Http\Controllers\AdminController::class, 'updateSettings'])->name('settings.update');
});

require __DIR__.'/auth.php';
