<?php
namespace Excursions\Admin\Model;

use Pipe\Db\Entity\Entity;
use Museums\Admin\Model\Museum;

class ExcursionMargin extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'excursions_margin',
            'properties' => [
                'depend'      => [],
                'tourists'    => [],
                'margin'      => [],
            ],
        ];
    }
}







