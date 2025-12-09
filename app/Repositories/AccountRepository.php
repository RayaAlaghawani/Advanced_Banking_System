<?php

namespace App\Repositories;

use App\Http\Requests\AccountRequest;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

class AccountRepository
{
    public function find(int $userId): ?Account
    {
        $user = Auth::user();
        $userId=$user->id;

        return Account::where('user_id',$userId)->first();
    }
    public function createMain(int $userId, AccountRequest $request): Account
    {
        return Account::create([
            'user_id' => $userId,
            'name' => $request->name,
            'account_type' => $request->account_type,
            'balance' => $request->balance ?? 0,
            'parent_id' => null,
            'status' => 'active',
        ]);
    }

    public function createSub(int $userId, AccountRequest $request,$account_id): Account
    {
        return Account::create(  [        'user_id' => $userId,
            'parent_id' => $account_id,
            'balance' => $request->balance,
            'account_type' => $request->account_type,
            'status' => 'active',

        ]);
    }

    public function update(Account $account): void
    {
        $account->save();
    }
}
