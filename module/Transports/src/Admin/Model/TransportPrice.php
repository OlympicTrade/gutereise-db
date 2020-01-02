<?php
namespace Transports\Admin\Model;

use Pipe\Db\Entity\Entity;

class TransportPrice extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'transports_price',
            'properties' => [
                'depend'      => [],
                'count'       => [],
                'price_day'   => [],
                'price_night' => [],
            ],
        ];
    }
}







