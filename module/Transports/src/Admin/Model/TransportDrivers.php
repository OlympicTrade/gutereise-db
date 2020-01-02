<?php
namespace Transports\Admin\Model;

use Pipe\Db\Entity\Entity;

class TransportDrivers extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'transports_drivers',
            'properties' => [
                'depend'        => [],
                'driver_id'     => [],
                'price_day'     => [],
                'price_night'   => [],
            ],
        ];
    }

    public function getPrice($duration)
    {
        return max($duration, 4) * $this->get('price');
    }
}







