<?php
namespace Orders\Admin\Model;

use Pipe\Db\Entity\Entity;

class OrderGCalendar extends Entity
{
    const SYNC_DISABLED = 0;
    const SYNC_SUCCESS  = 1;
    const SYNC_FAIL     = 2;

    static public function getFactoryConfig() {
        return [
            'table'      => 'order_gcalendar',
            'properties' => [
                'depend'      => [],
                'calendar_id' => [],
                'active'      => [],
            ],
            'plugins'    => [
                'emails' => function($model) {
                    return OrderGcalendarEmail::getEntityCollection();
                },
            ],
        ];
    }

    public function syncStatus()
    {
        if(!$this->get('active')) {
            return self::SYNC_DISABLED;
        }

        if($this->get('calendar_id')) {
            return self::SYNC_SUCCESS;
        }

        return self::SYNC_FAIL;
    }
}