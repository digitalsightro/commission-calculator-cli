<?php

namespace App\Service\ExchangeRates\ResponseBuilder;

use App\Service\ExchangeRates\Exceptions\ApiErrorException;
use App\Service\ExchangeRates\Exceptions\InvalidApiResponseException;
use App\Service\ExchangeRates\ExchangeRatesContext;

class ExchangeRatesApiResponseBuilder implements ResponseBuilderInterface
{
    /**
     * @throws InvalidApiResponseException
     * @throws ApiErrorException
     */
    public function buildResponse(array $response): ExchangeRatesContext
    {
        if (!empty($response['error'])) {
            throw new ApiErrorException($response['error']['code'], $response['error']['message']);
        }
        if (empty($response['rates'])) {
            throw new InvalidApiResponseException('Exchange Rates API Response is invalid.');
        }

        $exchangeRatesContext = new ExchangeRatesContext();
        $exchangeRatesContext->setRates($response['rates']);

        return $exchangeRatesContext;
    }
}
