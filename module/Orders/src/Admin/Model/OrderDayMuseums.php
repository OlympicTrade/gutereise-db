<?php
namespace Orders\Admin\Model;

use Pipe\Db\Entity\Entity;
use Museums\Admin\Model\Museum;

class OrderDayMuseums extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'orders_days_museums',
            'properties' => [

                'depend'        => [],
                'museum_id'     => [],
                'duration'      => [],
                'tickets_adults'    => [],
                'tickets_children'  => [],
                'guides'        => [],
                'extra'         => [],
                'outgo'         => [],
                'paid'          => [],
                'sort'          => [],
            ],
            'plugins'    => [
                'museum' => [
                    'factory' => function($model){
                        return new Museum(['id' => $model->get('museum_id')]);
                    },
                    'independent' => true,
                ],
            ],
        ];
    }

    /*public function __construct()
    {
        $this->setTable('orders_days_museums');

        $this->addProperties([
            'depend'        => [],
            'museum_id'     => [],
            'duration'      => [],
            'tickets_adults'    => [],
            'tickets_children'  => [],
            'guides'        => [],
            'extra'         => [],
            'outgo'         => [],
            'paid'          => [],
            'sort'          => [],
        ]);

        $this->addPlugin('museum', function($model) {
            $item = new Museum();
            $item->select()->where(['id' => $model->get('museum_id')]);
            return $item;
        }, ['independent' => true]);
    }*/
}