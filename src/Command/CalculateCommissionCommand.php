<?php

namespace App\Command;

use App\Service\CommissionCalculatorService;
use App\Service\Csv\CsvTransactionIterator;
use App\Service\ExchangeRates\ExchangeRatesContextProvider;
use App\Service\ExchangeRates\ExchangeRatesApiService;
use App\Service\ExchangeRates\ResponseBuilder\ExchangeRatesApiResponseBuilder;
use App\Service\Factory\CommissionStrategyFactory;
use App\Service\Transaction\TransactionsManager;
use App\Service\Validator\DefaultTransactionValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[AsCommand(name: 'app:calculate-commission')]
class CalculateCommissionCommand extends Command
{
    private CsvTransactionIterator $csvTransactionIterator;
    private CommissionStrategyFactory $commissionStrategyFactory;
    private TransactionsManager $transactionsManager;
    private ExchangeRatesContextProvider $exchangeRatesContextProvider;

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function __construct()
    {
        parent::__construct();
        $defaultTransactionValidator = new DefaultTransactionValidator();
        $this->csvTransactionIterator = new CsvTransactionIterator($defaultTransactionValidator);
        $this->transactionsManager = new TransactionsManager();
        $this->commissionStrategyFactory = new CommissionStrategyFactory();
        $httpClient = HttpClient::create();
        $exchangeRatesApiService = new ExchangeRatesApiService($httpClient);
        $exchangeRatesApiResponseBuilder = new ExchangeRatesApiResponseBuilder();
        $this->exchangeRatesContextProvider = new ExchangeRatesContextProvider(
            $exchangeRatesApiService,
            $exchangeRatesApiResponseBuilder
        );
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Calculates Commissions based on the given CSV file.')
            ->setHelp('This command allows you to calculate commissions based on the given CSV file.')
            ->addArgument('file', InputArgument::REQUIRED, 'The path to the Transactions CSV file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $status = Command::SUCCESS;
        try {
            $filePath = $input->getArgument('file');
            $calculator = new CommissionCalculatorService(
                $this->csvTransactionIterator,
                $this->transactionsManager,
                $this->commissionStrategyFactory,
                $this->exchangeRatesContextProvider
            );
            foreach ($calculator->calculate($filePath) as $commission) {
                $output->writeln($commission);
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $status = Command::FAILURE;
        } catch (\Throwable $e) {
            $output->writeln('<error>Unexpected error: ' . $e->getMessage() . '</error>');
            $status = Command::FAILURE;
        }

        return $status;
    }
}
