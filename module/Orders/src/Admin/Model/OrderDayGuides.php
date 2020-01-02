<?php
namespace Orders\Admin\Model;

use Pipe\Db\Entity\Entity;
use Guides\Admin\Model\Guide;

class OrderDayGuides extends Entity
{
    static public function getFactoryConfig()
    {
        return [
            'table'      => 'orders_days_guides',
            'properties' => [
                'depend'        => [],
                'guide_id'      => [],
                'duration'      => [],
                'income'        => [],
                'outgo'         => [],
                'paid'          => [],
                'payment_type'  => [],
                'sort'          => [],
            ],
            'plugins'    => [
                'guide' => [
                    'factory' => function($model){
                        return new Guide(['id' => $model->get('guide_id')]);
                    },
                    'options' => [
                        'independent' => true,
                    ],
                ],
            ],
        ];
    }

    /*public function __construct()
    {
        $this->setTable('orders_days_guides');

        $this->addProperties([
            'depend'        => [],
            'guide_id'      => [],
            'duration'      => [],
            'income'        => [],
            'outgo'         => [],
            'paid'          => [],
            'payment_type'  => [],
            'sort'          => [],
        ]);

        $this->addPlugin('guide', function($model) {
            $item = new Guide();
            $item->setId($model->get('guide_id'));
            return $item;
        }, ['independent' => true]);
    }*/
}