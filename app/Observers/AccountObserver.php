<?php

namespace App\Observers;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Exception;

class AccountObserver
{
    public function creating(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            $account = $transaction->account;


            if ($transaction->type === 'deposit') {
                $account->balance += $transaction->amount;
            } elseif ($transaction->type === 'withdrawal' || $transaction->type === 'transfer') {

                if (($account->balance - $transaction->amount) < $account->minimum_balance) {
                    throw new Exception('Transaction failed: Balance will fall below the minimum balance.');
                }
                $account->balance -= $transaction->amount;
            }

            $account->save();
        });
    }
}