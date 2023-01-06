<?php
    
    declare(strict_types=1);
    
    namespace PsTask\CommissionTask\Service;
    
    class Currency
    {
        private string $url = 'https://developers.paysera.com/tasks/api/currency-exchange-rates';
        private array $exchange_rates;
        
        public function __construct()
        {
            $data = file_get_contents($this->url);
            $this->exchange_rates = json_decode($data, true);
        }
    
        /**
         * @return array|mixed
         */
        public function getExchangeRates()
        {
            return $this->exchange_rates;
        }
    }