<?php
namespace Excursions\Admin\Model;

use Pipe\Db\Entity\Entity;
use Museums\Admin\Model\Museum;

class ExcursionExtra extends Entity
{
    const NATIONALITY_ALL       = 3;
    const NATIONALITY_RUSSIAN   = 1;
    const NATIONALITY_FOREIGN   = 2;

    static public $nationalityType = [
        self::NATIONALITY_ALL       => 'Все',
        self::NATIONALITY_RUSSIAN   => 'Русские',
        self::NATIONALITY_FOREIGN   => 'Иностранцы',
    ];

    const PRICE_GROUP    = 1;
    const PRICE_TOURIST  = 2;

    static public $priceType = [
        self::PRICE_GROUP     => 'За группу',
        self::PRICE_TOURIST   => 'За туриста',
    ];

    static public function getFactoryConfig() {
        return [
            'table'      => 'excursions_days_extra',
            'properties' => [
                'depend'         => [],
                'name'           => [],
                'proposal_name'  => [],
                'foreigners'     => [],
                'price_type'     => [],
                'tourists_from'  => [],
                'tourists_to'    => [],
                'income'         => [],
                'outgo'          => [],
            ],
        ];
    }

    public function getPrice($options)
    {
        $result = [
            'income' => $this->get('income'),
            'outgo'  => $this->get('outgo'),
            'desc'   => '',
        ];

        if($this->get('price_type') == self::PRICE_TOURIST) {
            $result['income'] *= $options['tourists'];
            $result['outgo']  *= $options['tourists'];
        }

        return $result;
    }
}