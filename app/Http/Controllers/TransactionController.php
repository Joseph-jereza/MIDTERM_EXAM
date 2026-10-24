<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $account = Account::findOrFail($request->account_id);

        if ($request->type === 'withdrawal') {
            if ($account->balance < $request->amount) {
                return response()->json(['message' => 'Insufficient balance.'], 400);
            }
            $account->balance -= $request->amount;
        } else {
            $account->balance += $request->amount;
        }

        $account->save();

        Transaction::create([
            'account_id' => $account->id,
            'type' => $request->type,
            'amount' => $request->amount,
            'reference_number' => strtoupper(Str::random(10)),
            'notes' => $request->notes,
        ]);

        return response()->json(['message' => 'Transaction successful!']);
    }
}