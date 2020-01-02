<?php
namespace Museums\Admin\Model;

use Pipe\Db\Entity\Entity;
use Museums\Admin\Model\Museum;

class MuseumExtra extends Entity
{
    /*const NATIONALITY_ALL       = 3;
    const NATIONALITY_RUSSIAN   = 1;
    const NATIONALITY_FOREIGN   = 2;

    static public $nationalityType = [
        self::NATIONALITY_ALL       => 'Все',
        self::NATIONALITY_RUSSIAN   => 'Русские',
        self::NATIONALITY_FOREIGN   => 'Иностранцы',
    ];*/

    const TRANSPORT_ALL    = 1;
    const TRANSPORT_AUTO   = 2;
    const TRANSPORT_WALK   = 3;

    static public $transportType = [
        self::TRANSPORT_ALL    => 'Любой',
        self::TRANSPORT_AUTO   => 'На авто',
        self::TRANSPORT_WALK   => 'Пешком',
    ];

    const PRICE_GROUP    = 1;
    const PRICE_TOURIST  = 2;

    static public $priceType = [
        self::PRICE_GROUP     => 'За группу',
        self::PRICE_TOURIST   => 'За туриста',
    ];

    static public function getFactoryConfig() {
        return [
            'table'      => 'museums_extra',
            'properties' => [
                'depend'         => [],
                'name'           => [],
                'proposal_name'  => [],
                'foreigners'     => [],
                'price_type'     => [],
                'transport_type' => [],
                'income'         => [],
                'outgo'          => [],
                'tourists_from'  => [],
                'tourists_to'    => [],
            ]
        ];
    }

    /*public function __construct()
    {
        $this->setTable('museums_extra');

        $this->addProperties([
            'depend'         => [],
            'name'           => [],
            'proposal_name'  => [],
            'foreigners'     => [],
            'price_type'     => [],
            'transport_type' => [],
            'income'         => [],
            'outgo'          => [],
            'tourists_from'  => [],
            'tourists_to'    => [],
        ]);
    }*/
}







