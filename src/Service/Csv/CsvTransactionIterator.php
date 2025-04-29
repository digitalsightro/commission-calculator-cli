<?php

namespace App\Service\Csv;

use App\Dto\Money;
use App\Dto\Transaction;
use App\Service\Validator\TransactionValidatorInterface;
use Iterator;
use RuntimeException;

class CsvTransactionIterator implements Iterator
{
    private $fileHandle;
    private string|null $filePath;
    private mixed $current;
    private int $key = 0;

    public function __construct(
        private readonly TransactionValidatorInterface $validator
    ) {
    }

    public function rewind(): void
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }

        if (!file_exists($this->filePath) || !is_readable($this->filePath)) {
            throw new RuntimeException("File $this->filePath doesnt exist or cannot be read.");
        }

        $this->fileHandle = fopen($this->filePath, 'r');

        if (!$this->fileHandle) {
            throw new RuntimeException("File $this->filePath cannot be opened.");
        }

        $this->next();
        $this->key = 0;
    }

    public function current(): mixed
    {
        return $this->current;
    }

    public function key(): int
    {
        return $this->key;
    }

    public function next(): void
    {
        $row = fgetcsv($this->fileHandle);
        $this->current = $row ? $this->mapToTransaction($row) : null;
        $this->key++;
    }

    public function valid(): bool
    {
        return $this->current !== null;
    }

    private function mapToTransaction(array $row): Transaction
    {
        $this->validator->validate($row);

        [$date, $userId, $userType, $operationType, $amount, $currency] = $row;

        return new Transaction(
            $date,
            $userId,
            $userType,
            $operationType,
            new Money((float)$amount, $currency)
        );
    }

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function __destruct()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }
}
