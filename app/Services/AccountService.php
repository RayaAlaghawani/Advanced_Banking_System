<?php
namespace App\Services;

use App\Composition\AccountInterface;
use App\Composition\CompositionAccount;
use App\Composition\LeafAccount;
use App\Http\Requests\AccountRequest;
use App\Models\Account;
use App\Repositories\AccountRepository;
use Illuminate\Support\Facades\Auth;

class AccountService
{
    public AccountRepository $repo;

    public function __construct(AccountRepository $repo)
    {
        $this->repo = $repo;
    }

    public function wrapAccount(Account $accountModel): AccountInterface
    {
        if ($accountModel->children->isEmpty()) {
            return new LeafAccount($accountModel);
        }
        return new CompositionAccount($accountModel);
    }

    public function createMainAccount(int $userId, AccountRequest $request): Account
    {
        return $this->repo->createMain($userId, $request);
    }

    public function createSubAccount(int $userId, AccountRequest $request): Account
    {
        $parent=$this->repo->find($userId);
        $parentId=$parent->id;
        return $this->repo->createSub($userId, $request, $parentId);
    }

}
