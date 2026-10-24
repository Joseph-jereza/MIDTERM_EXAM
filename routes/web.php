<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Models\Account;
use App\Models\Transaction;

// Ruta para sa pangunahing dashboard view
Route::get('/', function () {
    return view('dashboard');
});

// Ruta para sa pag-save ng transaksiyon (Deposit / Withdrawal)
Route::post('/transactions', [TransactionController::class, 'store']);

// Ruta para makuha ang detalye ng account gamit ang ID
Route::get('/api/accounts/{id}', function ($id) {
    $account = Account::find($id);
    if (!$account) {
        return response()->json(['message' => 'Not found'], 404);
    }
    return response()->json($account);
});

// Ruta para makuha ang listahan ng transaksiyon ng isang account
Route::get('/api/transactions/{accountId}', function ($accountId) {
    $transactions = Transaction::where('account_id', $accountId)->latest()->get();
    return response()->json($transactions);
});