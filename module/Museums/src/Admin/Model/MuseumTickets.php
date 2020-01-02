<?php
namespace Museums\Admin\Model;

use Application\Admin\Model\Nationality;
use Pipe\Db\Entity\Entity;

class MuseumTickets extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'museum_tickets',
            'properties' => [
                'depend'        => [],
                'adult_price'   => [],
                'child_price'   => [],
                'min_price'     => [],
                'date_from'     => ['type' => Entity::PROPERTY_TYPE_DATE],
                'date_to'       => ['type' => Entity::PROPERTY_TYPE_DATE],
                'date_reverse'  => [],
                'foreigners'    => [],
            ],
            'events' => [
                [
                    'events'   => [Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE],
                    'function' => function ($event) {
                        $model = $event->getTarget();
                        $model->set('date_reverse', (int) (($model->get('date_from', ['filter' => false]) > $model->get('date_to', ['filter' => false]))));
                        return true;
                    }
                ]
            ]
        ];
    }

    /*public function __construct()
    {
        $this->setTable('museum_tickets');

        $this->addProperties([
            'depend'        => [],
            'adult_price'   => [],
            'child_price'   => [],
            'min_price'     => [],
            'date_from'     => [],
            'date_to'       => [],
            'date_reverse'  => [],
            'foreigners'    => [],
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

        $this->getEventManager()->attach([Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE], function ($event) {
            $model = $event->getTarget();
            $model->set('date_reverse', (int) (($model->get('date_from', true) > $model->get('date_to', true))));

            return true;
        });
    }*/

    public function loadByDate($museumId, $dt, $foreigners = Nationality::NATIONALITY_ALL)
    {
        $this->select()->where
            ->equalTo('depend', $museumId)
            ->nest()
                ->equalTo('foreigners', $foreigners)
                ->or
                ->equalTo('foreigners', Nationality::NATIONALITY_ALL)
            ->unnest()
            ->nest()
                ->nest()
                    ->lessThanOrEqualTo('date_from', $dt->format('0000-m-d'))
                    ->greaterThanOrEqualTo('date_to', $dt->format('0000-m-d'))
                    ->equalTo('date_reverse', 0)
                ->unnest()
                ->or
                ->nest()
                    ->nest()
                        ->lessThanOrEqualTo('date_from', $dt->format('0000-m-d'))
                        ->or
                        ->greaterThanOrEqualTo('date_to', $dt->format('0000-m-d'))
                    ->unnest()
                    ->equalTo('date_reverse', 1)
                ->unnest()
            ->unnest();

        return $this->load();
    }
}







