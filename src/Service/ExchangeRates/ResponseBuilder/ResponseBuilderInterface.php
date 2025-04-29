<?php

namespace App\Service\ExchangeRates\ResponseBuilder;

interface ResponseBuilderInterface
{
    public function buildResponse(array $response);
}
