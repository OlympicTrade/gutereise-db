<?php
namespace Application\Admin\Model;

class Currency
{
    const CURRENCY_RUB  = 'rub';
    const CURRENCY_EUR  = 'eur';
    const CURRENCY_USD  = 'usd';

    static public $currencyTypes = [
        self::CURRENCY_RUB   => 'Рубли',
        self::CURRENCY_EUR   => 'Евро',
        self::CURRENCY_USD   => 'Доллар',
    ];

    protected $autoRound = [
        self::CURRENCY_RUB => 50,
        self::CURRENCY_EUR => 5,
        self::CURRENCY_USD => 5,
    ];

    protected $rate;
    protected $round;
    protected $currency;

    public function __construct($options = []) {
        $options = $options + [
            'currency' => self::CURRENCY_RUB,
            'round'    => 'auto',
            'rate'     => '',
        ];

        $this->setCurrency($options['currency'], $options['rate']);
        $this->round = $options['round'];
    }

    public function setCurrency($currency, $rate = '')
    {
        if(!array_key_exists($currency, self::$currencyTypes)) {
            throw new \Exception('Unknown currency type "' . $currency . '"');
        }

        $this->currency = $currency;

        if($rate > 0 && $currency != 'rub') {
            $this->rate = $rate;
            return $this;
        }

        if($currency != self::CURRENCY_RUB) {
            $settings = Settings::getInstance();
            switch ($currency) {
                case self::CURRENCY_EUR:
                    $this->rate = $settings->get('currency')->eur;
                    break;
                case self::CURRENCY_USD:
                    $this->rate = $settings->get('currency')->usd;
                    break;
            }
        } else {
            $this->rate = 1;
        }

        return $this;
    }

    public function getPrice($price, $round = true)
    {
        if(!$price) {
            return 0;
        }

        $price /= $this->rate;

        if(!$round) return round($price);

        if($this->round === 'auto') {
            $round = 50;
        } else {
            $round = $this->round;
        }

        if($this->rate > 50 && $price < 30) {
            return round($price, 1);
        }

        while($price < $round * 10) {
            $round /= 10;
        }

        if($round > 1) {
            return round(round($price / $round) * $round);
        }

        //$round = 0.5;
        $round *= 10;
        return round(round($price / ($round), 1) * $round, 1);
    }

    public function getRate() {
        return $this->rate;
    }
}