<?php
namespace Museums\Admin\Model;

use Pipe\Db\Entity\Entity;

class MuseumWorktime extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'museum_worktime',
            'properties' => [
                'depend'      => [],
                'weekday'     => [],
                'time_from'   => [],
                'time_to'     => [],
                'foreigners'  => [],
            ],
        ];
    }
}







