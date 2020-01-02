<?php
namespace Museums\Admin\Model;

use Pipe\Db\Entity\Entity;

class MuseumWeekends extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'museum_weekends',
            'properties' => [
                'depend'       => [],
                'date_from'    => ['type' => Entity::PROPERTY_TYPE_DATE],
                'date_to'      => ['type' => Entity::PROPERTY_TYPE_DATE],
            ],
        ];
    }

    /*public function __construct()
    {
        $this->setTable('museum_weekends');

        $this->addProperties([
            'depend'       => [],
            'date_from'    => [],
            'date_to'      => [],
        ]);

        $this->addPropertyFilterIn('date_from', function($model, $val) {
            list($day, $month) = explode('.', $val);
            return '0000-' . $month . '-' . $day;
        });

        $this->addPropertyFilterOut('date_from', function($model, $val) {
            list($year, $month, $day) = explode('-', $val);
            return $day . '.' . $month;
        });

        $this->addPropertyFilterIn('date_to', function($model, $val) {
            list($day, $month) = explode('.', $val);
            return '0000-' . $month . '-' . $day;
        });

        $this->addPropertyFilterOut('date_to', function($model, $val) {
            list($year, $month, $day) = explode('-', $val);
            return $day . '.' . $month;
        });
    }*/
}







