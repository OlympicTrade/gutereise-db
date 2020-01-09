<?php
namespace Orders\Admin\Model;

use Pipe\Db\Entity\Entity;
use Clients\Admin\Model\Client;

class OrderClients extends Entity
{
    static public function getFactoryConfig()
    {
        return [
            'table'      => 'orders_clients',
            'properties' => [
                'depend'        => [],
                'client_id'     => [],
                'paid'          => [],
                'payment_type'  => [],
                'sort'          => ['type' => Entity::PROPERTY_TYPE_NUM],
            ],
            'plugins'    => [
                'client' => [
                    'factory' => function($model){
                        //dd($model->client_id);
                        return new Client(['id' => $model->client_id]);
                    },
                    'independent' => true,
                ],
            ],
        ];
    }

    /*public function __construct()
    {
        $this->setTable('orders_clients');

        $this->addProperties([
            'depend'        => [],
            'client_id'     => [],
            'paid'          => [],
            'payment_type'  => [],
            'sort'          => ['type' => Entity::PROPERTY_TYPE_NUM],
        ]);

        $this->addPlugin('client', function($model) {
            $item = new Client();
            $item->setId($model->get('client_id'));
            return $item;
        }, ['independent' => true]);
    }*/
}