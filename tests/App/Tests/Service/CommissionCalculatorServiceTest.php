<?php

namespace App\Tests\Service;

use App\Service\CommissionCalculatorService;
use App\Service\ExchangeRates\ExchangeRatesContext;
use App\Service\ExchangeRates\ExchangeRatesContextProvider;
use App\Service\Factory\CommissionStrategyFactory;
use App\Service\Csv\CsvTransactionIterator;
use App\Service\ExchangeRates\ExchangeRatesApiService;
use App\Service\Transaction\TransactionsManager;
use App\Service\Validator\DefaultTransactionValidator;
use App\Service\Validator\Exception\TransactionValidationException;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorServiceTest extends TestCase
{
    public function testExampleInputReturnsExpectedCommissions(): void
    {
        $filePath = __DIR__ . '/test_data.csv';

        $validatorMock = new DefaultTransactionValidator();
        $csvIterator = new CsvTransactionIterator($validatorMock);
        $csvIterator->setFilePath($filePath);

        $transactionsManager = new TransactionsManager();
        $strategyFactory = new CommissionStrategyFactory();
        $exchangeRatesContext = new ExchangeRatesContext();
        $exchangeRatesContext->setRates([
            'EUR' => 1.0,
            'USD' => 1.1497,
            'JPY' => 129.53,
        ]);
        $exchangeRatesApiResponse = [
            'rates' => [
                'EUR' => 1.0,
                'USD' => 1.1497,
                'JPY' => 129.53,
            ]
        ];

        $exchangeServiceMock = $this->createMock(ExchangeRatesApiService::class);
        $exchangeServiceMock->method('getExchangeRates')
            ->willReturn($exchangeRatesApiResponse);

        $exchangeRatesContext = new ExchangeRatesContext();
        $exchangeRatesContext->setRates($exchangeRatesApiResponse['rates']);
        $exchangeRatesContextProvider = $this->createMock(ExchangeRatesContextProvider::class);
        $exchangeRatesContextProvider->method('provide')
            ->willReturn($exchangeRatesContext);

        $calculator = new CommissionCalculatorService(
            $csvIterator,
            $transactionsManager,
            $strategyFactory,
            $exchangeRatesContextProvider
        );

        $commissions = iterator_to_array($calculator->calculate($filePath));

        // Expected output from task description
        $expected = [
            0.6,
            3.0,
            0.0,
            0.06,
            1.5,
            0.0,
            0.7,
            0.3,
            0.3,
            3.0,
            0.0,
            0.0,
            8612.0,
        ];

        $this->assertCount(count($expected), $commissions);
        foreach ($expected as $index => $value) {
            $this->assertEquals($value, $commissions[$index], "Commission at index $index does not match");
        }
    }

    public function testInvalidTransactionThrowsValidationException(): void
    {
        $this->expectException(TransactionValidationException::class);

        $filePath = __DIR__ . '/invalid_test_data.csv';

        $validator = new DefaultTransactionValidator();
        $csvIterator = new CsvTransactionIterator($validator);
        $csvIterator->setFilePath($filePath);

        $transactionsManager = new TransactionsManager();
        $strategyFactory = new CommissionStrategyFactory();

        $exchangeRatesContext = new ExchangeRatesContext();
        $exchangeRatesContext->setRates([
            'EUR' => 1.0,
            'USD' => 1.1497,
            'JPY' => 129.53,
        ]);

        $exchangeRatesContextProvider = $this->createMock(ExchangeRatesContextProvider::class);
        $exchangeRatesContextProvider->method('provide')
            ->willReturn($exchangeRatesContext);

        $calculator = new CommissionCalculatorService(
            $csvIterator,
            $transactionsManager,
            $strategyFactory,
            $exchangeRatesContextProvider
        );

        iterator_to_array($calculator->calculate($filePath));
    }
}
