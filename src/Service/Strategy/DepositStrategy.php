<?php

namespace App\Service\Strategy;

use App\Service\ExchangeRates\ExchangeRatesContext;

/**
 * Strategy for calculating deposit commissions for clients.
 *
 * Dependencies:
 *  - ExchangeRatesContext: provides currency conversion rates for EUR-based limits.
 */
class DepositStrategy extends AbstractCommissionStrategy
{
    private const float COMMISSION_RATE = 0.0003;

    public function __construct(ExchangeRatesContext $exchangeRatesContext)
    {
        parent::__construct($exchangeRatesContext);
    }

    public function getCommissionRate(): float
    {
        return self::COMMISSION_RATE;
    }
}
