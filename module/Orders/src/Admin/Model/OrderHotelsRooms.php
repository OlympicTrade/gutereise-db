<?php
namespace Orders\Admin\Model;

use Pipe\Db\Entity\Entity;

class OrderHotelsRooms extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'orders_hotels_rooms',
            'properties' => [
                'depend'        => [],
                'hotel_id'      => [],
                'room_id'       => [],
                'tourists'      => [],
                'breakfast'     => [],
                'bed_size'      => [],
                'sort'          => ['type' => Entity::PROPERTY_TYPE_NUM],
            ],
        ];
    }
}