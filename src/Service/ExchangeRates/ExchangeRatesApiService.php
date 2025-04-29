<?php

namespace App\Service\ExchangeRates;

use App\Service\ExchangeRates\Exceptions\ApiErrorException;
use App\Service\ExchangeRates\Exceptions\InvalidApiResponseException;
use App\Service\ExchangeRates\Exceptions\MissingExchangeRateApiAccessKey;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeRatesApiService
{
    private const API_URL = "https://api.exchangeratesapi.io/v1/latest?access_key=%s&base=%s";
    private const BASE_CURRENCY = "EUR";

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws MissingExchangeRateApiAccessKey
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ApiErrorException
     */
    public function getExchangeRates(string $baseCurrency = self::BASE_CURRENCY): array
    {
        $accessKey = getenv('EXCHANGE_RATES_API_KEY');

        if (empty($accessKey)) {
            throw new MissingExchangeRateApiAccessKey('Missing exchange rates API key');
        }

        $url = sprintf(self::API_URL, $accessKey, $baseCurrency);
        try {
            $response = $this->client->request('GET', $url);
        } catch (\Exception) {
            throw new ApiErrorException('An error occurred while trying to do the API call.');
        }

        return $response->toArray();
    }
}
