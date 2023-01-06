<?php
    require __DIR__ . '/vendor/autoload.php';
    
    use PsTask\CommissionTask\Service\Calculator;
    use PsTask\CommissionTask\Service\Currency;
    
    $row = 1;
    if (($handle = fopen("input.csv", "r")) !== FALSE) {
    
        $currency = new Currency();
        $exchange_rates = $currency->getExchangeRates();
        Calculator::setExchangeRates($exchange_rates);
        
        while (($input = fgetcsv($handle, 0, ",")) !== FALSE) {
            try {
                $calculation = new Calculator(...$input);
                print("<pre>");
                print_r($calculation->getCalculatedCommission());
                print("</pre>");
                print("<hr/>");
            } catch (Exception $e) {
                echo $e;
            }
        }
        fclose($handle);
    }
