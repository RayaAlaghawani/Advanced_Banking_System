<?php
namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Services\AccountService;
use Illuminate\Http\Request;
use App\Repositories\AccountRepository;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    protected AccountService $service;

    public function __construct(AccountRepository $repo)
    {
        $this->service = new AccountService($repo);
    }

    public function createMainAccount(AccountRequest $request)
    {
        $userId = Auth::id();
        $account = $this->service->createMainAccount($userId, $request);
        return response()->json(['message' => 'Main account created', 'data' => $account]);
    }

    public function createSubAccount(AccountRequest $request)
    {
        $parentId = $request->parent_id;
        $account = $this->service->createSubAccount(Auth::id(), $request, $parentId);
        return response()->json(['message' => 'Sub-account created', 'data' => $account]);
    }

    public function transaction(Request $request)
    {
        $request->validate([
            'account_id' => 'required|integer|exists:accounts,id',
            'type' => 'required|in:deposit,withdraw,transfer',
            'amount' => 'required|numeric|min:1',
            'child_id' => 'nullable|integer',
            'target_id' => 'nullable|integer',
            'allow_recursive' => 'nullable|boolean'
        ]);

        $this->service->transaction(
            $request->account_id,
            $request->type,
            $request->amount,
            $request->child_id ?? null,
            $request->target_id ?? null,
            $request->allow_recursive ?? false
        );

        return response()->json(['message' => 'Transaction completed successfully']);
    }
}
