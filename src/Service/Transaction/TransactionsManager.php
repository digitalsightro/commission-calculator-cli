<?php

namespace App\Service\Transaction;

use App\Dto\Transaction;

class TransactionsManager
{
    private array $transactionsHistory = [];

    public function addTransaction(Transaction $transaction): void
    {
        $this->transactionsHistory[$transaction->getUserIndicatorNumber()][] = $transaction;
    }

    /**
     * @return Transaction[]
     */
    public function getTransactionsByUserIDN(int $userIDN): array
    {
        return $this->transactionsHistory[$userIDN] ?? [];
    }
}
