<?php
namespace Orders\Admin\Model;

use Pipe\Db\Entity\Entity;

use Zend\Session\Container as SessionContainer;

class OrderBalance extends Entity
{
    static public function getFactoryConfig()
    {
        return [
            'table'      => 'orders_balance',
            'properties' => [
                'order_id'     => [],
                'desc'     => [],
                'price'     => [],
                'type'     => [],
            ],
        ];
    }
}