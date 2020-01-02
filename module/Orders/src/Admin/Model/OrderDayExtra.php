<?php
namespace Orders\Admin\Model;

use Pipe\Db\Entity\Entity;

class OrderDayExtra extends Entity
{
    static public function getFactoryConfig()
    {
        return [
            'table'      => 'orders_days_extra',
            'properties' => [
                'depend'        => [],
                'name'          => [],
                'proposal_name' => [],
                'per_person'    => [],
                'income'        => [],
                'outgo'         => [],
                'payment_type'  => [],
                'sort'          => [],
            ],
        ];
    }

    /*public function __construct()
    {
        $this->setTable('orders_days_extra');

        $this->addProperties([
            'depend'        => [],
            'name'          => [],
            'proposal_name' => [],
            'income'        => [],
            'outgo'         => [],
            'payment_type'  => [],
            'sort'          => [],
        ]);
    }*/
}