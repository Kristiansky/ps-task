<?php
    
    declare(strict_types=1);
    
    namespace PsTask\CommissionTask\Tests\Service;
    
    use PHPUnit\Framework\TestCase;
    use PsTask\CommissionTask\Service\Calculator;
    use PsTask\CommissionTask\Service\Currency;
    
    class CalculatorTest extends TestCase
    {
    
        /**
         * @param string $date
         * @param int $uid
         * @param string $utype
         * @param string $otype
         * @param float $amount
         * @param string $currency
         * @param string $expectation
         * @return void
         * @throws \Exception
         *
         * @dataProvider dataProviderForAddTesting
         */
        public function testGetCalculatedCommission(string $date, int $uid, string $utype, string $otype, float $amount, string $currency, string $expectation)
        {
            $currency_rates = new Currency();
            $exchange_rates = $currency_rates->getExchangeRates();
            Calculator::setExchangeRates($exchange_rates);
            $calculator = new Calculator($date, $uid, $utype, $otype, $amount, $currency);
            
            $this->assertEquals(
                $expectation,
                $calculator->getCalculatedCommission()
            );
        }
    
        public function dataProviderForAddTesting(): array
        {
            return [
                'calculate commission of 1200 Euro withdrawal' => ["2014-12-31", 4, "private", "withdraw", 1200.00, "EUR", "0.60 EUR"],
                'calculate commission of 1000 Euro withdrawal in the same week by the same customer' => ["2015-01-01", 4, "private", "withdraw", 1000.00, "EUR", "3.00 EUR"],
                'calculate commission of 1000 Euro withdrawal after a year by the same customer' => ["2016-01-05", 4, "private", "withdraw", 1000.00, "EUR", "0.00 EUR"],
                'calculate commission of 200 Euro deposit' => ["2016-01-05", 1, "private", "deposit", 200.00, "EUR", "0.06 EUR"],
                'calculate commission of 300 Euro business deposit' => ["2016-01-06", 2, "business", "withdraw", 300.00, "EUR", "1.50 EUR"],
                'calculate commission of 30000 JPY withdrawal' => ["2016-01-06", 1, "private", "withdraw", 30000, "JPY", "0.00 JPY"],
                'calculate commission of 1000 EUR withdrawal' => ["2016-01-07", 1, "private", "withdraw", 1000.00, "EUR", "0.69 EUR"],
                'calculate commission of 100 USD withdrawal' => ["2016-01-07", 1, "private", "withdraw", 100.00, "USD", "0.30 USD"],
                'calculate commission of 100 EUR withdrawal' => ["2016-01-10", 1, "private", "withdraw", 100.00, "EUR", "0.30 EUR"],
                'calculate commission of 10000 EUR business deposit' => ["2016-01-10", 2, "business", "deposit", 10000.00, "EUR", "3.00 EUR"],
                'calculate commission of 1000 EUR private withdrawal' => ["2016-01-10", 3, "private", "withdraw", 1000.00, "EUR", "0.00 EUR"],
                'calculate commission of 300 EUR private withdrawal' => ["2016-02-15", 1, "private", "withdraw", 300.00, "EUR", "0.00 EUR"],
                'calculate commission of 3000000 JPY private withdrawal' => ["2016-02-19", 5, "private", "withdraw", 3000000, "JPY", "8,607.39 JPY"], // This last withdrawal may throw error on testing because of the EUR to JPY fluctuation of rate
            ];
        }
    }