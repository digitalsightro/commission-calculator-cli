<?php

namespace App\Service\Strategy;

use App\Dto\Transaction;

interface CommissionCalculateStrategyInterface
{
    public function calculate(Transaction $transaction): float;
}
