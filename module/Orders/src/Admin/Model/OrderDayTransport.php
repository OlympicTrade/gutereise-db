<?php
namespace Orders\Admin\Model;

use Pipe\Db\Entity\Entity;
use Drivers\Admin\Model\Driver;
use Transports\Admin\Model\Transport;

class OrderDayTransport extends Entity
{
    static public function getFactoryConfig()
    {
        return [
            'table' => 'orders_days_transport',
            'properties' => [
                'depend' => [],
                'transport_id' => [],
                'driver_id' => [],
                'duration' => [],
                'passengers' => [],
                'income' => [],
                'outgo' => [],
                'paid' => [],
                'payment_type' => [],
                'sort' => [],
            ],
            'plugins' => [
                'transport' => [
                    'factory' => function ($model) {
                        return new Transport(['id' => $model->get('transport_id')]);
                    },
                    'independent' => true,
                ],
                'driver' => [
                    'factory' => function ($model) {
                        return new Driver(['id' => $model->get('driver_id')]);
                    },
                    'independent' => true,
                ],
            ],
        ];
    }

    /*public function __construct()
    {
        $this->setTable('orders_days_transport');

        $this->addProperties([
            'depend'        => [],
            'transport_id'  => [],
            'driver_id'     => [],
            'duration'      => [],
            'passengers'    => [],
            'income'        => [],
            'outgo'         => [],
            'paid'          => [],
            'payment_type'  => [],
            'sort'          => [],
        ]);

        $this->addPlugin('transport', function($model) {
            $item = new Transport();
            $item->select()->where(['id' => $model->get('transport_id')]);
            return $item;
        }, ['independent' => true]);

        $this->addPlugin('driver', function($model) {
            $item = new Driver();
            $item->select()->where(['id' => $model->get('driver_id')]);
            return $item;
        }, ['independent' => true]);
    }*/
}