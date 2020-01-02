<?php
namespace Orders\Admin\Model;

use Pipe\Db\Entity\Entity;

class OrderGCalendarEmail extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'order_gcalendar_emails',
            'properties' => [
                'depend'        => [],
                'email'         => [],
                'active'        => [],
            ],
        ];
    }
}