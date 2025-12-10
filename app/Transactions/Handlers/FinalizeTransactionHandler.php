<?php

namespace App\Transactions\Handlers;

use App\Models\Transaction;

class FinalizeTransactionHandler extends BaseHandler
{
    protected function check(Transaction $transaction)
    {
        $from = $transaction->fromAccount;
        $to   = $transaction->toAccount;

        if ($transaction->type === Transaction::TYPE_WITHDRAW && $from) {
            $from->balance -= $transaction->amount;
            $from->save();
        }

        if ($transaction->type === Transaction::TYPE_DEPOSIT && $to) {
            $to->balance += $transaction->amount;
            $to->save();
        }

        if ($transaction->type === Transaction::TYPE_TRANSFER && $from && $to) {
            $from->balance -= $transaction->amount;
            $to->balance += $transaction->amount;
            $from->save();
            $to->save();
        }

        $transaction->status = Transaction::STATUS_COMPLETED;
        $transaction->save();
    }
}
