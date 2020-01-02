<?php
namespace Excursions\Admin\Model;

use Pipe\Db\Entity\Entity;

use Zend\Session\Container as SessionContainer;

class ExcursionGuides extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'excursions_days_guides',
            'properties' => [
                'depend'       => [],
                'tourists'     => [],
                'guides'       => [],
                'foreigners'   => [],
                'sort'         => [],
            ],
        ];
    }
}







