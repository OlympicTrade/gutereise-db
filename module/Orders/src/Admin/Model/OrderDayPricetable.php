<?php
namespace Orders\Admin\Model;

use Pipe\Db\Entity\Entity;

class OrderDayPricetable extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'orders_days_pricetable',
            'properties' => [
                'depend'        => [],
                'name'          => [],
                'sort'          => [],
            ],
        ];
    }
}