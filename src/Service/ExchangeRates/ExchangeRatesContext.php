<?php

namespace App\Service\ExchangeRates;

use App\Service\ExchangeRates\Exceptions\MissingExchangeRateException;

class ExchangeRatesContext
{
    private array $exchangeRates = [];

    public function setRates(array $rates): void
    {
        $this->exchangeRates = $rates;
    }

    /**
     * @throws MissingExchangeRateException
     */
    public function getRate(string $currency): float
    {
        return $this->exchangeRates[$currency] ?? throw new MissingExchangeRateException("Missing rate for $currency");
    }
}
