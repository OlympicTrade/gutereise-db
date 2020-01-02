<?php
namespace Excursions\Admin\Model;

use Pipe\Db\Entity\Entity;

class ExcursionTimetable extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'excursions_days_timetable',
            'properties' => [
                'depend'      => [],
                'name'        => [],
                'duration'    => [],
                'tourists_from'  => [],
                'tourists_to'    => [],
                'foreigners'  => [],
                'sort'        => [],
            ],
        ];
    }
}







