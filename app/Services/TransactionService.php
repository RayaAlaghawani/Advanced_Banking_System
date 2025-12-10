<?php

namespace App\Services;

use App\Models\Transaction;
use App\Transactions\Handlers\ValidationHandler;
use App\Transactions\Handlers\BalanceCheckHandler;
use App\Transactions\Handlers\AutoApprovalHandler;
use App\Transactions\Handlers\TellerApprovalHandler;
use App\Transactions\Handlers\ManagerApprovalHandler;
use App\Transactions\Handlers\FinalizeTransactionHandler;

class TransactionService
{
    public function createAndProcess(array $data)
    {
        // 1) أنشئ المعاملة في قاعدة البيانات
        $transaction = Transaction::create($data);

        // 2) ابني السلسلة Chain
        $chain = $this->buildChain();

        // 3) مرّر المعاملة عبر السلسلة
        $chain->handle($transaction);

        return $transaction;
    }

    private function buildChain()
    {
        $validation       = new ValidationHandler();
        $balanceCheck     = new BalanceCheckHandler();
        $autoApproval     = new AutoApprovalHandler();
        $tellerApproval   = new TellerApprovalHandler();
        $managerApproval  = new ManagerApprovalHandler();
        $finalize         = new FinalizeTransactionHandler();

        // بناء السلسلة
        $validation
            ->setNext($balanceCheck)
            ->setNext($autoApproval)
            ->setNext($tellerApproval)
            ->setNext($managerApproval)
            ->setNext($finalize);

        return $validation;
    }
}
