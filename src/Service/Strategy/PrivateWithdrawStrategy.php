<?php

namespace App\Service\Strategy;

use App\Dto\Transaction;
use App\Service\ExchangeRates\Exceptions\MissingExchangeRateException;
use App\Service\ExchangeRates\ExchangeRatesContext;
use App\Service\Transaction\TransactionsManager;

/**
 * Strategy for calculating withdrawal commissions for private clients.
 *
 * Dependencies:
 *  - TransactionsManager: tracks and retrieves the user’s past transactions.
 *  - ExchangeRatesContext: provides currency conversion rates for EUR-based limits.
 */
class PrivateWithdrawStrategy extends AbstractCommissionStrategy
{
    private const float COMMISSION_RATE = 0.003;
    private const int MAX_FREE_WITHDRAWS_PER_WEEK = 3;
    private const int MAX_FREE_EUR_PER_WEEK = 1000;

    private TransactionsManager $transactionsManager;

    public function __construct(
        TransactionsManager $transactionsManager,
        ExchangeRatesContext $exchangeRatesContext
    ) {
        parent::__construct($exchangeRatesContext);
        $this->transactionsManager = $transactionsManager;
    }

    /**
     * @throws MissingExchangeRateException
     */
    public function calculate(Transaction $transaction): float
    {
        $history = $this->transactionsManager->getTransactionsByUserIDN($transaction->getUserIndicatorNumber());
        $weekTransactions = $this->filterThisWeekTransactions($transaction, $history);

        if (!$this->requiresCommission($weekTransactions)) {
            return 0.0;
        }

        $transactionRate = $this->getRate($transaction->getMoney()->getCurrency());
        $amountEUR = $transaction->getMoney()->getAmount() / $transactionRate;
        $totalWeekAmountEUR = $this->calculateTotalWeekAmountEUR($weekTransactions);

        $excessEUR = max(0, $totalWeekAmountEUR - self::MAX_FREE_EUR_PER_WEEK);
        $chargeableAmountEur = min($excessEUR, $amountEUR);

        $commissionInEUR = $chargeableAmountEur * self::COMMISSION_RATE;
        $commissionInOriginal = $commissionInEUR * $transactionRate;

        return $this->roundUp($commissionInOriginal, $transaction->getMoney()->getCurrency());
    }

    private function calculateTotalWeekAmountEUR(array $transactions): float
    {
        return array_reduce($transactions, function ($carry, Transaction $t) {
            if ($t->getOperationType() !== Transaction::WITHDRAW_OPERATION_TYPE) {
                return $carry;
            }

            $amountEUR = $t->getMoney()->getAmount() / $this->getRate($t->getMoney()->getCurrency());
            return $carry + $amountEUR;
        }, 0.0);
    }

    private function filterThisWeekTransactions(Transaction $current, array $history): array
    {
        return array_filter($history, fn(Transaction $t) =>
            $t->getTransactionWeek() === $current->getTransactionWeek() &&
            $t->getTransactionYear() === $current->getTransactionYear());
    }

    private function requiresCommission(array $weekTransactions): bool
    {
        return $this->exceedsFreeWithdrawals($weekTransactions) || $this->exceedsFreeAmount($weekTransactions);
    }

    private function exceedsFreeWithdrawals(array $weekTransactions): bool
    {
        return count($weekTransactions) > self::MAX_FREE_WITHDRAWS_PER_WEEK;
    }

    private function exceedsFreeAmount(array $weekTransactions): bool
    {
        return $this->calculateTotalWeekAmountEUR($weekTransactions) > self::MAX_FREE_EUR_PER_WEEK;
    }
}
