<?php
    
    declare(strict_types=1);
    
    namespace PsTask\CommissionTask\Service;
    
    use DateTime;
    use Exception;
    
    class Calculator
    {
        private DateTime $date;
        private $uid;
        private $utype;
        private $otype;
        private $amount;
        private $currency;
        private $commission;
        static public array $withdrawals;
        static private array $exchange_rates;
    
        /**
         * @throws Exception
         */
        public function __construct(string $date, int $uid, string $utype, string $otype, float $amount, string $currency)
        {
            $date = new DateTime($date);
            $this->date = $date;
            $this->uid = $uid;
            $this->utype = $utype;
            $this->otype = $otype;
            $this->amount = $amount;
            $this->currency = $currency;
            if($otype === "withdraw" && $utype === "private"){
                $euro_amount = $amount / self::$exchange_rates['rates'][$currency];
                self::$withdrawals[$uid][] = array(
                    'date' => $date,
                    'amount' => $euro_amount,
                    'taxed_amount' => 0,
                );
            }
            $this->calculateCommission();
        }
    
        /**
         * @return void
         */
        private function calculateCommission(): void
        {
            if ($this->otype === "deposit") {
                $this->commission = $this->amount * 0.0003;
            } elseif ($this->otype === "withdraw") {
                if ($this->utype === "private") {
                    $user_withdrawals = self::$withdrawals[$this->uid];
                    $user_withdrawals_for_week = array_filter($user_withdrawals, function ($user_withdrawals){
                        if (($user_withdrawals['date']->format('W') === $this->date->format('W'))
                            && ($user_withdrawals['date']->format('o') === $this->date->format('o'))) {
                            return $user_withdrawals;
                        } else {
                            return false;
                        }
                    });
                    $user_amount = array_sum(array_column($user_withdrawals_for_week,'amount'));
                    $user_taxed_amount = array_sum(array_column($user_withdrawals_for_week,'taxed_amount'));
                    if ($user_amount > 1000) {
                        $exceeded_amount = $user_amount - 1000;
                        $exceeded_amount = $exceeded_amount - $user_taxed_amount;
                        $this->commission = $exceeded_amount * 0.003;
                        self::$withdrawals[$this->uid][array_key_last(self::$withdrawals[$this->uid])]['taxed_amount'] = $exceeded_amount;
                    } else {
                        if (count($user_withdrawals_for_week) > 3) {
                            $this->commission = end($user_withdrawals_for_week)['amount'] * 0.003;
                        }
                    }
                    if ($this->currency !== "EUR") {
                        $this->commission = $this->commission * self::$exchange_rates['rates'][$this->currency];
                    }
                } elseif ($this->utype === "business") {
                    $this->commission = $this->amount * 0.005;
                }
            }
        }
    
        /**
         * @param array $exchange_rates
         */
        public static function setExchangeRates(array $exchange_rates): void
        {
            self::$exchange_rates = $exchange_rates;
        }
    
        /**
         * @return string
         */
        public function getCalculatedCommission(): string
        {
            return number_format(round($this->commission, 2), 2) . " " . $this->currency;
        }
    }
