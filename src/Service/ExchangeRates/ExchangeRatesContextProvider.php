<?php

namespace App\Service\ExchangeRates;

use App\Service\ExchangeRates\Exceptions\ApiErrorException;
use App\Service\ExchangeRates\Exceptions\MissingExchangeRateApiAccessKey;
use App\Service\ExchangeRates\ResponseBuilder\ResponseBuilderInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ExchangeRatesContextProvider
{
    public function __construct(
        private readonly ExchangeRatesApiService $exchangeRatesApiService,
        private readonly ResponseBuilderInterface $responseBuilder
    ) {
    }

    /**
     * @throws MissingExchangeRateApiAccessKey
     * @throws ApiErrorException
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function provide(): ExchangeRatesContext
    {
        $response = $this->exchangeRatesApiService->getExchangeRates();

        return $this->responseBuilder->buildResponse($response);
    }
}
