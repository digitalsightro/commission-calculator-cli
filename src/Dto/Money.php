<?php

namespace App\Dto;

class Money
{
    public const array NO_DECIMAL_CENTS_CURRENCIES = ['JPY', 'HUF', 'KRW', 'VND', 'CLP'];
    private float $amount;
    private string $currency;

    public function __construct(
        float $amount,
        string $currency
    ) {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
