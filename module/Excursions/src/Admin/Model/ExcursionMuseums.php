<?php
namespace Excursions\Admin\Model;

use Pipe\Db\Entity\Entity;
use Museums\Admin\Model\Museum;

class ExcursionMuseums extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'excursions_days_museums',
            'properties' => [
                'depend'       => [],
                'museum_id'    => [],
                'foreigners'   => [],
                'duration'     => [],
                'sort'         => [],
            ],
        ];
    }
}







