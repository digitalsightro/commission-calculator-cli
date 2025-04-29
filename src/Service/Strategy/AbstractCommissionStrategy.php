<?php

namespace App\Service\Strategy;

use App\Dto\Money;
use App\Dto\Transaction;
use App\Service\ExchangeRates\Exceptions\MissingExchangeRateException;
use App\Service\ExchangeRates\ExchangeRatesContext;

abstract class AbstractCommissionStrategy implements CommissionCalculateStrategyInterface
{
    public function __construct(
        private readonly ExchangeRatesContext $exchangeRatesContext
    ) {
    }

    public function calculate(Transaction $transaction): float
    {
        $commission = $transaction->getTransactionAmount() * $this->getCommissionRate();
        return $this->roundUp($commission, $transaction->getTransactionCurrency());
    }

    protected function roundUp(float $amount, string $currency): float
    {
        $precision = in_array($currency, Money::NO_DECIMAL_CENTS_CURRENCIES) ? 0 : 2;

        $multiplier = 10 ** $precision;

        return ceil($amount * $multiplier - 1e-8) / $multiplier;
    }
    /**
     * @throws MissingExchangeRateException
     */
    protected function getRate(string $currency): float
    {
        return $this->exchangeRatesContext->getRate($currency);
    }
}
