<?php
namespace App\Composition;

use App\Models\Account;

class LeafAccount implements AccountInterface
{
    protected Account $model;

    public function __construct(Account $account)
    {
        $this->model = $account;
    }

    public function withdraw(float $amount): void
    {
        if ($this->model->balance < $amount) {
            throw new \Exception("Insufficient funds in account {$this->getId()}");
        }
        $this->model->balance -= $amount;
        $this->model->save();
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

    public function getBalance(): float
    {
        return (float) $this->model->balance;
    }

    public function getId(): int
    {
        return $this->model->id;
    }

    public function addChild(AccountInterface $child): void {}
    public function removeChild(int $childId): void {}
    public function getChild(int $childId): ?AccountInterface { return null; }
}
