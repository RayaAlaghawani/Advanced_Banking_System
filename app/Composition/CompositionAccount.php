<?php
namespace App\Composition;

use App\Models\Account;

class CompositionAccount implements AccountInterface
{
    protected Account $model;
    protected array $children = [];

    public function __construct(Account $account)
    {
        $this->model = $account;
        foreach ($account->children as $childModel) {
            if ($childModel->children->isEmpty()) {
                $this->children[] = new LeafAccount($childModel);
            } else {
                $this->children[] = new CompositionAccount($childModel);
            }
        }
    }

    public function withdraw(float $amount): void
    {
        if ($this->model->balance < $amount) {
            throw new \Exception("Insufficient funds in parent account {$this->getId()}");
        }
        $this->model->balance -= $amount;
        $this->model->save();
    }

    public function withdrawRecursive(float $amount): void
    {
        if ($this->model->balance >= $amount) {
            $this->withdraw($amount);
            return;
        }

        $remaining = $amount - $this->model->balance;
        $this->withdraw($this->model->balance);

        foreach ($this->children as $child) {
            $childBalance = $child->getBalance();
            $toWithdraw = min($childBalance, $remaining);
            if ($toWithdraw > 0) {
                $child->withdraw($toWithdraw);
                $remaining -= $toWithdraw;
            }
            if ($remaining <= 0) break;
        }

        if ($remaining > 0) {
            throw new \Exception("Insufficient funds in parent and children accounts");
        }
    }

    public function deposit(float $amount): void
    {
        $this->model->balance += $amount;
        $this->model->save();
    }

    public function transfer(AccountInterface $target, float $amount): void
    {
        $this->withdraw($amount);
        $target->deposit($amount);
    }

    public function withdrawFromChild(int $childId, float $amount): void
    {
        $child = $this->getChild($childId);
        if (!$child) throw new \Exception("Child account not found");
        $child->withdraw($amount);
    }

    public function depositToChild(int $childId, float $amount): void
    {
        $child = $this->getChild($childId);
        if (!$child) throw new \Exception("Child account not found");
        $child->deposit($amount);
    }

    public function addChild(AccountInterface $child): void
    {
        $this->children[] = $child;
    }

    public function removeChild(int $childId): void
    {
        foreach ($this->children as $key => $child) {
            if ($child->getId() === $childId) {
                unset($this->children[$key]);
                $this->children = array_values($this->children);
                return;
            }
        }
    }

    public function getChild(int $childId): ?AccountInterface
    {
        foreach ($this->children as $child) {
            if ($child->getId() === $childId) return $child;
            if ($child instanceof CompositionAccount) {
                $found = $child->getChild($childId);
                if ($found) return $found;
            }
        }
        return null;
    }

    public function getBalance(): float
    {
        $total = $this->model->balance;
        foreach ($this->children as $child) {
            $total += $child->getBalance();
        }
        return $total;
    }

    public function getId(): int
    {
        return $this->model->id;
    }
}
