<?php

namespace App\Service\Validator;

use App\Dto\Transaction;
use App\Service\Validator\Exception\TransactionValidationException;
use DateTime;

class DefaultTransactionValidator implements TransactionValidatorInterface
{
    private const int EXPECTED_COLUMNS = 6;
    private const array VALID_CLIENT_TYPES = [Transaction::PRIVATE_CLIENT, Transaction::BUSINESS_CLIENT];
    private const array VALID_OPERATION_TYPES = [
        Transaction::WITHDRAW_OPERATION_TYPE,
        Transaction::DEPOSIT_OPERATION_TYPE
    ];

    /**
     * @throws TransactionValidationException
     */
    public function validate(array $data): void
    {
        $this->validateNumberOfFields($data);

        [$date, $userId, $userType, $operationType, $amount, $currency] = $data;

        $this->validateDate($date);
        $this->validateUserId($userId);
        $this->validateUserType($userType);
        $this->validateOperationType($operationType);
        $this->validateAmount($amount);
        $this->validateCurrency($currency);
    }

    /**
     * @throws TransactionValidationException
     */
    private function validateNumberOfFields(array $data): void
    {
        if (count($data) !== self::EXPECTED_COLUMNS) {
            throw new TransactionValidationException("Invalid number of fields: expected 6, got " . count($data));
        }
    }

    /**
     * @throws TransactionValidationException
     */
    private function validateDate(string $date): void
    {
        if (!DateTime::createFromFormat('Y-m-d', $date)) {
            throw new TransactionValidationException("Invalid date format: $date");
        }
    }

    /**
     * @throws TransactionValidationException
     */
    private function validateUserId(string $userId): void
    {
        if (!filter_var($userId, FILTER_VALIDATE_INT)) {
            throw new TransactionValidationException("Invalid user ID: $userId");
        }
    }

    /**
     * @throws TransactionValidationException
     */
    private function validateUserType(string $userType): void
    {
        if (!in_array($userType, self::VALID_CLIENT_TYPES, true)) {
            throw new TransactionValidationException("Unsupported user type: $userType");
        }
    }

    /**
     * @throws TransactionValidationException
     */
    private function validateOperationType(string $operationType): void
    {
        if (!in_array($operationType, self::VALID_OPERATION_TYPES, true)) {
            throw new TransactionValidationException("Unsupported operation type: $operationType");
        }
    }

    /**
     * @throws TransactionValidationException
     */
    private function validateAmount(string $amount): void
    {
        if (!is_numeric($amount)) {
            throw new TransactionValidationException("Invalid amount: $amount");
        }
    }

    /**
     * @throws TransactionValidationException
     */
    private function validateCurrency(string $currency): void
    {
        if (!ctype_upper($currency) || strlen($currency) !== 3) {
            throw new TransactionValidationException("Invalid currency code: $currency");
        }
    }
}
