<?php

namespace App\Service\Strategy;

use App\Service\ExchangeRates\ExchangeRatesContext;

/**
 * Strategy for calculating withdrawal commissions for business clients.
 *
 * Dependencies:
 *  - ExchangeRatesContext: provides currency conversion rates for EUR-based limits.
 */
class BusinessWithdrawStrategy extends AbstractCommissionStrategy
{
    private const float COMMISSION_RATE = 0.005;

    public function __construct(ExchangeRatesContext $exchangeRatesContext)
    {
        parent::__construct($exchangeRatesContext);
    }

    public function getCommissionRate(): float
    {
        return self::COMMISSION_RATE;
    }
}
