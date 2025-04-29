<?php

namespace App\Service;

use App\Dto\Transaction;
use App\Service\Csv\CsvTransactionIterator;
use App\Service\ExchangeRates\ExchangeRatesContextProvider;
use App\Service\ExchangeRates\ExchangeRatesApiService;
use App\Service\Factory\CommissionStrategyFactory;
use App\Service\Transaction\TransactionsManager;

readonly class CommissionCalculatorService
{
    public function __construct(
        private CsvTransactionIterator $csvIterator,
        private TransactionsManager $transactionsManager,
        private CommissionStrategyFactory $strategyFactory,
        private ExchangeRatesContextProvider $exchangeContextProvider
    ) {
    }

    /**
     * Process a CSV file of transactions and calculate commission fees on the fly.
     *
     * - Loads the CSV from the given file path.
     * - Retrieves current exchange rates.
     * - Iterates each Transaction record.
     * - Tracks transaction history for applying weekly limits.
     * - Selects and invokes the right commission-calculation strategy.
     * - Yields each computed commission as a float.
     *
     * @return iterable<float>
     */
    public function calculate(string $filePath): iterable
    {
        $this->csvIterator->setFilePath($filePath);
        $exchangeRates = $this->exchangeContextProvider->provide();

        /** @var Transaction $transaction */
        foreach ($this->csvIterator as $transaction) {
            $this->transactionsManager->addTransaction($transaction);

            $strategy = $this->strategyFactory->create(
                $transaction->getUserType(),
                $transaction->getOperationType(),
                $this->transactionsManager,
                $exchangeRates
            );

            yield $strategy->calculate($transaction);
        }
    }
}
