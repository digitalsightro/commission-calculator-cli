<?php

namespace App\Dto;

class Transaction
{
    public const string PRIVATE_CLIENT = 'private';
    public const string BUSINESS_CLIENT = 'business';
    public const string WITHDRAW_OPERATION_TYPE = 'withdraw';
    public const string DEPOSIT_OPERATION_TYPE = 'deposit';
    private string $date;
    private int $userIndicatorNumber;
    private string $userType;
    private string $operationType;
    private Money $money;

    public function __construct(
        string $date,
        int $userIndicatorNumber,
        string $userType,
        string $operationType,
        Money $money
    ) {
        $this->setDate($date);
        $this->setUserIndicatorNumber($userIndicatorNumber);
        $this->setUserType($userType);
        $this->setOperationType($operationType);
        $this->setMoney($money);
    }

    public function setDate(string $date): Transaction
    {
        $this->date = $date;
        return $this;
    }

    public function getUserIndicatorNumber(): int
    {
        return $this->userIndicatorNumber;
    }

    public function setUserIndicatorNumber(int $userIndicatorNumber): Transaction
    {
        $this->userIndicatorNumber = $userIndicatorNumber;
        return $this;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function setUserType(string $userType): Transaction
    {
        $this->userType = $userType;
        return $this;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }

    public function setOperationType(string $operationType): Transaction
    {
        $this->operationType = $operationType;
        return $this;
    }

    public function getMoney(): Money
    {
        return $this->money;
    }

    public function setMoney(Money $money): Transaction
    {
        $this->money = $money;
        return $this;
    }

    public function getTransactionWeek(): string
    {
        return date('W', strtotime($this->date));
    }

    public function getTransactionYear(): string
    {
        return date('o', strtotime($this->date));
    }

    public function getTransactionAmount(): float
    {
        return $this->money->getAmount();
    }

    public function getTransactionCurrency(): string
    {
        return $this->money->getCurrency();
    }
}
