<?php
namespace Guides\Admin\Model;

use Pipe\Db\Entity\Entity;

class GuideCalendar extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'guides_calendar',
            'properties' => [
                'depend'      => [],
                'date'        => [],
                'busy'        => [],
            ],
        ];
    }
}






