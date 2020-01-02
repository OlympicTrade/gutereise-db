<?php
namespace Hotels\Admin\Model;

use Pipe\Db\Entity\Entity;

class HotelRoomPrice extends Entity
{
    static public function getFactoryConfig()
    {
        return [
            'table'      => 'hotels_rooms_price',
            'properties' => [
                'depend'        => [],
                'price'         => [],
                'tourists'      => [],
                'date_from'     => ['type' => Entity::PROPERTY_TYPE_DATE],
                'date_to'       => ['type' => Entity::PROPERTY_TYPE_DATE],
                'date_reverse'  => [],
            ],
            'events' => [
                [
                    'event'    => [Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE],
                    'function' => function ($event) {
                        $model = $event->getTarget();
                        $model->set('date_reverse', (int) ($model->get('date_from', true) > $model->get('date_to', true)));
                        return true;
                    }
                ]
            ]
        ];
    }
}