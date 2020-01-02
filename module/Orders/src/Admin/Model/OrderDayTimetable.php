<?php
namespace Orders\Admin\Model;

use Pipe\Db\Entity\Entity;

class OrderDayTimetable extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'orders_days_timetable',
            'properties' => [
                'depend'        => [],
                'name'          => [],
                'duration'      => [],
                'sort'          => [],
            ],
        ];
    }
}