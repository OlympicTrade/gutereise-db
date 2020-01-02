<?php
namespace Transports\Admin\Model;

use Pipe\DateTime\Time;
use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\EntityCollection;
use Sync\Admin\Service\PriceService;
use Translator\Admin\Model\Translator;
use Transports\Admin\Service\TransportsService;

class Transport extends Entity
{
    const TYPE_AUTO  = 1;
    const TYPE_WATER = 2;
    const TYPE_ALL   = 3;

    static public $types = [
        self::TYPE_AUTO  => 'Авто транспорт',
        self::TYPE_WATER => 'Водный транспорт',
    ];

    static public function getFactoryConfig() {
        return [
            'table'      => 'transports',
            'properties' => [
                'name'        => [],
                'genitive1'   => [],
                'genitive2'   => [],
                'type'        => [],
                'capacity'    => [],
                'min_price'   => [],
                'comment'     => [],
            ],
            'plugins'    => [
                'price' => function($model) {
                    return EntityCollection::factory(TransportPrice::class);
                },
                'drivers' => [
                    'factory' => function($model, $options){
                        if($options['driver_id']) {
                            $driverPrice = new TransportDrivers();
                            $driverPrice->select()->where([
                                'driver_id' => $options['driver_id'],
                            ]);
                            return $driverPrice;
                        }

                        $drivers = TransportDrivers::getEntityCollection();
                        return $drivers;
                    },
                    'options' => [
                        'independent' => true,
                    ],
                ],
            ],
            'events' => [
                'events' => [
                    'events'   => [Entity::EVENT_POST_INSERT, Entity::EVENT_POST_UPDATE],
                    'function' => function ($event) {
                        PriceService::syncRequest('transport', $model = $event->getTarget()->id());
                        return true;
                    }
                ],
            ]
        ];
    }
    /*public function __construct($options = [])
    {
        parent::__construct($options);

        $this->setTable('transports');

        $this->addProperties([
            'name'        => [],
            'genitive1'   => [],
            'genitive2'   => [],
            'type'        => [],
            'capacity'    => [],
            'min_price'   => [],
            'comment'     => [],
        ]);

        $this->addPlugin('price', function($model) {
            $price = TransportPrice::getEntityCollection();
            return $price;
        });

        $this->addPlugin('drivers', function($model, $options = []) {
            if($options['driver_id']) {
                $driverPrice = new TransportDrivers();
                $driverPrice->select()->where([
                    'driver_id' => $options['driver_id'],
                ]);
                return $driverPrice;
            }

            $drivers = TransportDrivers::getEntityCollection();
            return $drivers;
        });

        $this->getEventManager()->attach(array(Entity::EVENT_POST_INSERT, Entity::EVENT_POST_UPDATE), function ($event) {
            PriceService::syncRequest('transport', $model = $event->getTarget()->id());

            return true;
        });

        Translator::setModelEvents($this, ['include' => ['name', 'genitive1']]);
    }*/

    public function getUrl()
    {
        return '/transports/edit/' . $this->id() . '/';
    }

    public function getPrice($options)
    {
    	$this->load();
        $result = [
            'desc'   => 0,
            'income' => 0,
            'outgo'  => 0,
            'errors' => [],
        ];

        $desc = $this->get('name') . ': ';
    		
        if($options['count'] > $this->get('capacity')) {
            $result['errors'][TransportsService::ERROR_CAPACITY] = $this->get('name') . ': только ' . $this->get('capacity') . ' мест';
            return $result;
        }

        $tPrice = new TransportPrice();
        $tPrice->select()
            ->order('count DESC')
            ->where
                ->equalTo('depend', $this->id())
                ->lessThanOrEqualTo('count', $options['count']);

        if(!$tPrice->load()) {
            $result['errors'][TransportsService::ERROR_PRICE] = $this->get('name') . ': цены не найдены';
            return $result;
        }

        //Мин аренда 4 часа
        $duration = Time::getDT($options['duration']);

        if(!$this->get('min_price') && $duration->format('G') < 4) {
            $duration->setTime(4);
        }

        $timeFrom = Time::getDT($options['time']);

        $timeTo = (clone $timeFrom)->addition($duration);
        $dateRange = new \DatePeriod((clone $timeFrom)->round('up')->getDtObj(), new \DateInterval('PT1H'), (clone $timeTo)->round('down')->getDtObj());

        //Hours
        $hours = ['day' => 0, 'night' => 0];
        foreach($dateRange as $dt){
            $hour = $dt->format('G');
            if($hour >= 22 || $hour <= 7) {
                $hours['night']++;
            } else {
                $hours['day']++;
            }
        }

        //Minutes
        if($mins = $timeFrom->getMinutes()) {
            $hour = $timeFrom->format('G');
            if($hour >= 22 || $hour <= 7) {
                $hours['night'] += round((60 - $mins) / 60, 2);
            } else {
                $hours['day'] += round((60 - $mins) / 60, 2);
            }
        }

        if($mins = $timeTo->getMinutes()) {
            $hour = $timeTo->format('G');
            if($hour >= 22 || $hour <= 7) {
                $hours['night'] += round($mins / 60, 2);
            } else {
                $hours['day'] += round($mins / 60, 2);
            }
        }

        //Если все часы в аренде транспорта ночные и только 1 час приходится на лень, то его стоит считать ночным
        if($hours['night'] > 1 && $hours['day'] == 1) {
            $hours['night']++;
            $hours['day']--;
        }

        $income = ($hours['day'] * $tPrice->get('price_day')) + ($hours['night'] * $tPrice->get('price_night'));

        if($options['driver_id']) {
            $dPrice = $this->plugin('drivers', ['driver_id' => $options['driver_id']]);
            $outgo  = ($hours['day'] * $dPrice->get('price_day')) + ($hours['night'] * $dPrice->get('price_night'));
        } else {
            $outgo = $income;
        }

        $income = round($income / 10) * 10;
        $outgo = round($outgo / 10) * 10;

        if($income < $this->get('min_price')) {
            $income = $this->get('min_price');
            $outgo = $this->get('min_price');
            $desc .= 'Мин. стомиость ' . $income;
        } else {
            $desc .= $hours['day']   ? 'Д: ' . $hours['day']   . ' час. * ' . $tPrice->get('price_day') .  ', ' : '';
            $desc .= $hours['night'] ? 'Н: ' . $hours['night'] . ' час. * ' . $tPrice->get('price_night') .  ', ' : '';
        }

        $result['income']   = $income;
        $result['outgo']    = $outgo;
        $result['desc']     = rtrim($desc, ', ');

        return $result;
    }
}







