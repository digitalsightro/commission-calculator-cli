## 📁 Input Format

The input is a CSV file with the following columns:

date,user_id,user_type,operation_type,amount,currency

Example:

2016-01-10,2,business,deposit,10000.00,EUR


## ✅ Output Format

The output is a list of commission fees (one per line), calculated according to predefined rules.

Example:

0.6

3

0

0.06

1.5

0


## 🏗 Architecture

This app follows a modular and testable architecture:

- `src/Command/` – Symfony Console Command
- `src/Dto/` – Data Transfer Objects (e.g. Transaction, Money)
- `src/Service/Service` –  Class that reads transactions from a CSV file and allows them to be processed one by one using PHP’s Iterator interface.
- `src/Service/Strategy/` – Strategy design pattern for commission calculation
- `src/Service/Exception/` – General App Exceptions
- `src/Service/ExchangeRates` – Handles the API Call and Response Building of the ExchangesRates API (https://api.exchangeratesapi.io/v1/latest)
- `src/Service/Factory` – Factory design pattern for each operation types
- `src/Service/Transaction` –  Responsible for keeping track of current and previously processed transactions. It allows commission strategies to make decisions based on a user's transaction history.
- `src/Service/CommissionCalculatorService.php` – The core service responsible for calculating the commission fees for a list of financial transactions from the CSV file.
- `src/Service/Validator/` – Validations for the CSV file rows
- `src/Service/EnvLoader.php` – Class designed to manually load environment variables from a .env file into the application's runtime.
- `tests/` – PHPUnit-based test suite

## ⚙️ Requirements

- PHP 8.3+
- Composer (https://getcomposer.org/doc/00-intro.md) 

## 🧪 Running the Application

Install dependencies:

```bash
composer install
```

Run the command with your CSV input:
```bash
php bin/console app:calculate-commission path/to/input.csv
```

## 🧪 Running PhpUnit Test
Run this command:
```bash
vendor/bin/phpunit
```

## 🧪 Verify PSR-12
Run this command:
```bash
vendor/bin/phpcs --standard=PSR12 src/
```

## Time: Aprox. 8h
