<?php

namespace Drivers\Admin\Service;

use Pipe\Mvc\Service\Admin\TableService;
use Drivers\Admin\Model\Driver;

class DriversService extends TableService
{
    public function getDrivers($props)
    {
        $drivers = Driver::getEntityCollection();
        $drivers->select()->columns(['id']);

        if($props['transport_id']) {
            $drivers->select()
                ->join(['td' => 'transports_drivers'], 'td.driver_id = t.id', [])
                ->where(['td.depend' => $props['transport_id']]);
        }

        $result = [];
        foreach ($drivers as $driver) {
            $result[] = [
                'id'     => (string) $driver->id(),
                'status' => 1,
            ];
        }

        return $result;
    }
}