<?php

namespace App\Service\Factory;

use App\Dto\Transaction;
use App\Service\Exception\UnknownOperationTypeException;
use App\Service\Exception\UnknownUserTypeException;
use App\Service\ExchangeRates\ExchangeRatesContext;
use App\Service\Strategy\BusinessWithdrawStrategy;
use App\Service\Strategy\CommissionCalculateStrategyInterface;
use App\Service\Strategy\DepositStrategy;
use App\Service\Strategy\PrivateWithdrawStrategy;
use App\Service\Transaction\TransactionsManager;

class CommissionStrategyFactory
{
    /**
     * Creates the appropriate commission calculation strategy based on the transaction type and user type.
     *
     * - Maps deposit and withdrawal operations to specific strategies.
     * - Validates operation type and user type to ensure they are supported.
     * - Instantiates and returns the corresponding strategy based on the user type and operation type.
     *
     * @param string $userType The type of user (e.g., private or business).
     * @param string $operationType The type of operation (e.g., deposit or withdraw).
     * @param TransactionsManager $transactionsManager Manages transaction data and limits.
     * @param ExchangeRatesContext $exchangeRatesContext Holds exchange rates used in calculations.
     * @return CommissionCalculateStrategyInterface The selected strategy for commission calculation.
     * @throws UnknownOperationTypeException If the operation type is not supported.
     * @throws UnknownUserTypeException If the user type is not supported for the given operation type.
     */
    public function create(
        string $userType,
        string $operationType,
        TransactionsManager $transactionsManager,
        ExchangeRatesContext $exchangeRatesContext
    ): CommissionCalculateStrategyInterface {
        $depositStrategies = [
            Transaction::DEPOSIT_OPERATION_TYPE => DepositStrategy::class,
            Transaction::WITHDRAW_OPERATION_TYPE => [
                Transaction::PRIVATE_CLIENT => PrivateWithdrawStrategy::class,
                Transaction::BUSINESS_CLIENT => BusinessWithdrawStrategy::class,
            ],
        ];

        if (!isset($depositStrategies[$operationType])) {
            throw new UnknownOperationTypeException("Operation type $operationType not supported!");
        }

        if (
            $operationType === Transaction::WITHDRAW_OPERATION_TYPE
            && !isset($depositStrategies[$operationType][$userType])
        ) {
            throw new UnknownUserTypeException("Unknown user type: $userType");
        }

        if ($operationType === Transaction::DEPOSIT_OPERATION_TYPE) {
            return new $depositStrategies[$operationType]($exchangeRatesContext);
        }

        if ($operationType === Transaction::WITHDRAW_OPERATION_TYPE && $userType === Transaction::PRIVATE_CLIENT) {
            return new $depositStrategies[$operationType][$userType]($transactionsManager, $exchangeRatesContext);
        }

        return new $depositStrategies[$operationType][$userType]($exchangeRatesContext);
    }
}
