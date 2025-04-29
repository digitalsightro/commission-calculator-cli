<?php

namespace App\Service\Validator;

interface TransactionValidatorInterface
{
    public function validate(array $data): void;
}
