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

    /**
     * تنفيذ عملية سحب، إيداع أو تحويل
     * @param int $accountId
     * @param string $type ['withdraw','deposit','transfer']
     * @param float $amount
     * @param int|null $childId - إذا حددنا ابن سيتم العمل عليه
     * @param int|null $targetId - لحالات التحويل
     * @param bool $allowRecursiveFromChildren - للسحب من أبنـاء إذا الأب غير كافي
     */
    public function transaction(int $accountId, string $type, float $amount, ?int $childId = null, ?int $targetId = null, bool $allowRecursiveFromChildren = false): void
    {
        $accountModel = $this->repo->find($accountId);
        if (!$accountModel) throw new \Exception("Account not found");

        $account = $this->wrapAccount($accountModel);

        switch ($type) {
            case 'withdraw':
                if ($childId !== null) {
                    $account->withdrawFromChild($childId, $amount);
                } else {
                    if ($allowRecursiveFromChildren && $account instanceof CompositionAccount) {
                        $account->withdrawRecursive($amount);
                    } else {
                        $account->withdraw($amount);
                    }
                }
                break;

            case 'deposit':
                if ($childId !== null) {
                    $account->depositToChild($childId, $amount);
                } else {
                    $account->deposit($amount);
                }
                break;

            case 'transfer':
                if (!$targetId) throw new \Exception("Target account required for transfer");
                $targetModel = $this->repo->find($targetId);
                if (!$targetModel) throw new \Exception("Target account not found");

                $target = $this->wrapAccount($targetModel);
                $account->transfer($target, $amount);
                break;

            default:
                throw new \Exception("Invalid transaction type");
        }

        $this->repo->update($accountModel);
    }
}
