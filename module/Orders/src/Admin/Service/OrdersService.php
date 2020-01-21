<?php

namespace Orders\Admin\Service;

use Pipe\Db\Entity\EntityCollection;
use Pipe\Db\Entity\ConfigCollector;
use Excursions\Admin\Model\ExcursionDay;
use Orders\Admin\Model\Order;
use Orders\Admin\Model\OrderDay;
use Pipe\Mvc\Service\Admin\TableService;
use Pipe\String\Translit;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class OrdersService extends TableService
{
    const ERROR_GUIDE_NOT_SELECTED  = 401;
    const ERROR_DRIVER_NOT_SELECTED = 402;
    const ERROR_CLIENT_NOT_SELECTED = 403;
    const ERROR_TRANSPORT_NOT_SUITABLE = 404;

    public function delOrderDay($dayId)
    {
        $day = new OrderDay();
        $day->id($dayId);

        if($day->load()) {
            $day->remove();
        }
    }

    public function addOrderDay($orderId)
    {
        $newDay = new OrderDay();
        $newDay->setVariables([
            'depend'    => $orderId,
            'time_from' => '12:00:00',
        ]);

        $lastDay = new OrderDay();
        $lastDay->select()
            ->where(['depend' => $orderId])
            ->order('date DESC');

        if($lastDay->load()) {
            $newDay->set('date', \Pipe\DateTime\Date::getDT($lastDay->get('date'))->modify('+1 day')->format(), true);
        } else {
            $newDay->set('date', \Pipe\DateTime\Date::getDT('NOW')->modify('+1 day')->format(), true);
        }

        $newDay->save();

        return $newDay;
    }

    public function getOrders($filters)
    {
        $orders = Order::getEntityCollection();

        $select = $this->getListSelect($filters);

        $orders->setSelect($select);

        return $orders;
    }

    protected function saveModelBefore($order, $data)
    {
        return $order;
    }

    public function saveModelAfter($order, $data = [])
    {
        $this->updateOrderDate($order);
        $this->getGCalendarService()->updateGoogleCalendar($order);

        $order->save();

        return $order;
    }

    public function getOrderEmails($order)
    {
        $emails = [];

        foreach ($order->plugin('clients') as $orderClient) {
            if(!$orderClient->get('client_id')) {
                continue;
            }

            $client = $orderClient->plugin('client');

            $i = 0;
            foreach ($client->getEmails() as $email) {
                $i++;
                $emails['c' . $client->id() . '-' . $i] = [
                    'name'  => 'Клиент: ' . $client->get('name') . ' (' . $email . ')',
                    'email' => $email,
                ];
            }
        }

        foreach ($order->plugin('days') as $day) {
            foreach ($day->plugin('guides') as $dayGuide) {
                if(!$dayGuide->get('guide_id')) {
                    continue;
                }

                $guide = $dayGuide->plugin('guide')->load();

                $i = 0;
                foreach ($guide->getEmails() as $email) {
                    $i++;
                    $emails['g' . $guide->id() . '-' . $i] = [
                        'name'  => 'Гид: ' . $guide->get('name') . ' (' . $email . ')',
                        'email' => $email,
                    ];
                }
            }

            foreach ($day->plugin('transports') as $dayTransport) {
                if(!$dayTransport->get('driver_id')) {
                    continue;
                }

                $driver = $dayTransport->plugin('driver')->load();
                $i = 0;
                foreach ($driver->getEmails() as $email) {
                    $i++;
                    $emails['d' . $driver->id() . '-' . $i] = [
                        'name'  => 'Водитель: ' . $driver->get('name') . ' (' . $email . ')',
                        'email' => $email,
                    ];
                }
            }
        }

        return $emails;
    }

    public function updateOrderDate(Order $order)
    {
        $firstDay = '0000-00-00';
        $lastDay = '0000-00-00';
        $days = 0;
        foreach($order->plugin('days') as $day) {
            $firstDay = $firstDay != '0000-00-00' ? $firstDay : $day->get('date');
            $lastDay = $day->get('date');
            $days++;
        }

        $order->setVariables([
            'date_from'  => $firstDay,
            'date_to'    => $lastDay,
            'days_count' => $days,
        ]);

        return $order;
    }

    public function getListSelect($filters = [])
    {
        $order = new Order();
        $collection = $order->getCollection();

        $select = $collection->select();
        $select
            //->join(['oc' => 'orders_clients'], 't.id = oc.depend', [], 'left')
            ->group('t.id');

        if(!empty($filters)) {
            if($filters['guide_id']) {
                $select
                    ->join(['od'  => 'orders_days'], 'od.depend = t.id', [])
                    ->join(['odg' => 'orders_days_guides'], 'odg.depend = od.id', [])
                    ->where(['odg.guide_id' => $filters['guide_id']]);
            }

            if($filters['search']) {
                $select->where->like('t.name', '%' . $filters['search'] . '%');
            }

            if($filters['manager_id']) {
                $select->where(['t.manager_id' => $filters['manager_id']]);
            }

            if($filters['client_id']) {
                $select->where(['oc.client_id' => $filters['client_id']]);
            }

            if($filters['order_status']) {
                $select->where(['t.status' => $filters['order_status']]);
            }

            if($filters['date_from']) {
                $select->where->lessThanOrEqualTo('t.date_from', $filters['date_from']);
            }

            if($filters['date_to']) {
                $select->where->greaterThanOrEqualTo('t.date_to', $filters['date_to']);
            }

            /*if($filters['debts']) {
                switch($filters['debts']) {
                    case 1:
                        $select->where
                            ->notEqualTo('t.driver_price', 't.driver_price_paid', Where::TYPE_IDENTIFIER, Where::TYPE_IDENTIFIER)
                            ->or
                            ->notEqualTo('t.client_price', 't.client_price_paid', Where::TYPE_IDENTIFIER, Where::TYPE_IDENTIFIER)
                            ->or
                            ->notEqualTo('t.guide_price', 't.guide_price_paid', Where::TYPE_IDENTIFIER, Where::TYPE_IDENTIFIER);
                        break;
                    case 2:
                        $select->where(array(
                            new Expression('t.driver_price <> t.driver_price_paid'),
                        ));
                        break;
                    case 3:
                        $select->where(array(
                            new Expression('t.guide_price <> t.guide_price_paid'),
                        ));
                        break;
                    case 4:
                        $select->where(array(
                            new Expression('t.client_price <> t.client_price_paid'),
                        ));
                        break;
                    default:
                }
            }*/
        }

        return $select;
    }

    public function calcOrder(Order $order, $options = [])
    {
        $dataToCalc = [
            'lang_id'        => $order->get('lang_id'),
            'kp_lang'        => $order->options['proposal']['lang'],
            'currency'       => $order->options['currency']['currency'],
            'currency_rate'  => $order->options['currency']['rate'],
            'adults'         => $order->get('adults'),
            'children'       => $order->get('children'),
            'margin'         => $order->get('margin'),
            'manager_id'     => $order->get('manager_id'),
            'agency'         => $order->get('agency'),
            'calc_type'      => $options['calc_type'],
            'clients'        => [],
            'days'           => [],
        ];

        $tourists = $order->get('adults') + $order->get('children');

        $income = 0;

        $dayId = 1;
        $orderAutocalc = true;

        //$order->days()->d();
        foreach ($order->days() as $day) {
            $dataToCalc['days'][$dayId] = $this->getFormalizeDayData($day, $tourists);
            $dayId++;
        }

        $dataToCalc['hotels'] = $this->getFormalizeHotelsData($order);

        $clients = [];
        foreach ($order->clients() as $oClient) {
            $client = $oClient->client();
            $clients[] = [
                'id' => $oClient->get('client_id'),
                'name'      => $client->get('name'),
                'phone'     => $client->getPhones()[0],
            ];
        }
        $dataToCalc['clients'] = $clients;

        $orderErrors = [];

        //summary
        if(!$orderAutocalc) {
            $allTourist = $order->get('adults') + $order->get('children');
            $adult = round(($income / $allTourist) / 10) * 10;

            $dataToCalc['summary'] = [
                'adult' => $adult,
                'child' => $adult,
                'outgo' => 0,
                'income' => $adult * $allTourist,
            ];
        }

        $calcData = $this->getCalcService()->calc($dataToCalc);
        $calcData['errors'] += $orderErrors;

        foreach ($calcData['days'] as $dayId => &$calcDay) {
            $orderDayData = $dataToCalc['days'][$dayId];

            foreach ($calcDay['guides']['list'] as &$guideData) {
                $guideData['order_errors'] = [];

                if(!$guideData['guide_id']) {
                    $guideData['order_errors'][self::ERROR_GUIDE_NOT_SELECTED] = 'Гид не выбран';
                }
            }

            foreach ($calcDay['transports']['list'] as &$transportData) {
                $transportData['order_errors'] = [];

                if(!$transportData['driver_id']) {
                    $transportData['order_errors'][self::ERROR_DRIVER_NOT_SELECTED] = 'Водитель не выбран';
                }
            }

            $calcDay['museums']['order_errors'] = $orderDayData['errors']['museums'];
        }

        return $calcData;
    }

    public function getFormalizeHotelsData($order)
    {
        $hotelsData = [
            'days_count' => $order->get('options')->hotels->days_count
        ];

        foreach($order->hotels() as $roomRow) {
            $hotelsData['hotels'][$roomRow['hotel_id']]['id'] = $roomRow->hotel_id;
            $hotelsData['hotels'][$roomRow['hotel_id']]['rooms'][] = [
                'id'        => $roomRow->room_id,
                'tourists'  => $roomRow->tourists,
                'breakfast' => $roomRow->breakfast,
                'bed_size'  => $roomRow->bed_size,
            ];
        }

        return $hotelsData;
    }

    public function getFormalizeDayData($day, $tourists)
    {
        $errors = [
            'transports' => [],
            'museums'    => [],
            'guides'     => [],
        ];

        $dayData = [
            'day_id'         => $day->day_id,
            'margin'         => $day->margin,
            'date'           => $day->date->format('Y-m-d'),
            'time'           => $day->time->format(),
            'transfer_time'  => $day->transfer_time,
            'transfer_id'    => $day->transfer_id,
            'car_delivery_time'  => $day->car_delivery_time,
            'duration'       => $day->duration,
            'guides'         => [
                'list'  => [],
                'income' => 0,
                'count' => 0
            ]
        ];

        $dayProposal = $day->options['proposal'];

        $dOptions = $day->options;

        $dayData['proposal']['place_start'] = $dayProposal['place_start'];
        $dayData['proposal']['place_end'] = $dayProposal['place_end'];

        $dayData['extra']['autocalc'] = $dOptions['extra']['autocalc'];
        $dayData['extra']['list'] = [];
        foreach ($day->extra() as $row) {
            $income = $row->per_person ? $row->per_person * $tourists : $row->income;

            $dayData['extra']['list'][] = [
                'name'          => $row->name,
                'proposal_name' => $row->proposal_name,
                'income'        => $income,
                'outgo'         => $row->outgo,
                'errors'        => [],
            ];
        }

        $dayData['proposal']['pricetable']['autocalc'] = $dOptions['proposal']['pricetable']['autocalc'];
        $dayData['proposal']['pricetable']['list'] = [];
        foreach ($day->pricetable() as $row) {
            $dayData['proposal']['pricetable']['list'][] = $row->name;
        }

        $dayData['proposal']['timetable']['autocalc'] = $dOptions['proposal']['timetable']['autocalc'];
        $dayData['proposal']['timetable']['list'] = [];
        foreach ($day->timetable() as $row) {
            $dayData['proposal']['timetable']['list'][] = [
                'name'     => $row->name,
                'duration' => $row->duration,
            ];
        }

        $dayData['guides']['autocalc'] = $dOptions['guides']['autocalc'];
        if(!$dOptions['guides']['autocalc']) {
            foreach ($day->guides() as $guide) {
                $newGuide = [
                    'errors'    => [],
                    'duration'  => $guide->duration,
                    'guide_id'  => $guide->guide_id,
                    'income'    => $guide->income,
                    'outgo'     => $guide->outgo,
                ];

                $dayData['guides']['count'] += 1;
                $dayData['guides']['list'][] = $newGuide;
            }
        }

        foreach ($day->museums() as $museum) {
            $newMuseum = [
                'id'                => $museum->museum_id,
                'duration'          => $museum->duration,
                'tickets_adults'    => $museum->tickets_adults,
                'tickets_children'  => $museum->tickets_children,
                'guides'            => $museum->guides,
                'extra'             => $museum->extra,
                'outgo'             => $museum->outgo,
            ];

            $dayData['museums'][] = $newMuseum;
        }

        $totalPassengers = 0;

        if($dOptions['transports']['autocalc']) {
            $exDay = new ExcursionDay(['id' => $dayData['day_id']]);

            foreach ($exDay->plugin('transport') as $row) {
                $dayData['transports'][] = [
                    'id'            => $row->get('transport_id'),
                    'duration'      => $row->get('duration'),
                    'type'          => $row->get('type'),
                ];
            }
        } else {
            foreach ($day->plugin('transports') as $dTransport) {
                if(!$dTransport->get('transport_id')) continue;

                $newTransport = [
                    'id'        => $dTransport->get('transport_id'),
                    'income'    => $dTransport->get('income'),
                    'outgo'     => $dTransport->get('outgo'),
                    'duration'  => $dTransport->get('duration'),
                    'count'     => $dTransport->get('passengers'),
                    'driver_id' => $dTransport->get('driver_id'),
                    'type'      => $dTransport->plugin('transport')->get('type'),
                ];

                $totalPassengers += $dTransport->get('passengers');

                $dayData['transports'][] = $newTransport;
            }
        }

        if($totalPassengers !== $tourists) {
            $errors['transports'][self::ERROR_TRANSPORT_NOT_SUITABLE] = 'Кол-во пассажиров (' . $totalPassengers . ') не равно кол-ву туристов (' . $tourists . ')';
        }

        $dayData['errors'] = $errors;

        return $dayData;
    }

    public function getOrderProposalHtml($order)
    {
        $calcData = $this->getOrderProposalData($order);

        return $this->getProposalService()->getProposalHtml($calcData);
    }

    public function getOrderProposalData($order, $options = [])
    {
        $calcData = $this->calcOrder($order, ['calc_type' => 'proposal']);

        if(!$order->options['proposal']['generalize']) {
            return $this->getProposalService()->getProposalData($calcData, $options);
        }

        $days = $order->days();
        if($days->count() > 1) {
            $daysIds = [];
            foreach ($days as $day) {
                $daysIds[] = $day->id();
            }

            $proposal = [];

            $select = $this->getSql()->select();
            $select
                ->from(['odt' => 'orders_days_transport'])
                ->columns(['transport_id', 'duration' => new Expression('SEC_TO_TIME(SUM(TIME_TO_SEC(odt.duration)))')])
                ->join(['d' => 'orders_days'], 'd.id = odt.depend', [])
                ->group('transport_id')
                ->where(['odt.depend' => $daysIds]);

            $proposal['transports'] = $this->execute($select);

            $select = $this->getSql()->select('orders_days_guides');
            $select
                ->columns(['guide_id', 'duration' => new Expression('SEC_TO_TIME(SUM(TIME_TO_SEC(duration)))')])
                ->where(['depend' => $daysIds]);
            $proposal['guides'] = $this->execute($select);

            $select = $this->getSql()->select('orders_days_extra');
            $select
                ->columns(['name' => 'proposal_name'])
                ->where(['depend' => $daysIds]);
            $proposal['extra'] = $this->execute($select);

            $calcData['order']['proposal']['pricetable'] = $proposal;
        }

        return $this->getProposalService()->getProposalData($calcData, $options);
    }

    protected function setFilters(EntityCollection $collection, $filters)
    {
        if($filters['query']) {
            $queries = Translit::searchVariants($filters['query']);

            $where = new Where();
            foreach ($queries as $query) {
                $where
                    ->like('t.name', '%' . $query . '%')
                    ->or
                    ->like('c.name', '%' . $query . '%')
                    ->or;
            }

            $collection->select()->where->addPredicate($where);

            $collection->select()
                ->group('t.id')
                ->join(['oc' => 'orders_clients'], 'oc.depend = t.id', [], 'left')
                ->join(['c' => 'clients'], 'c.id = oc.client_id', [],'left')
                ->where
                    //->greaterThanOrEqualTo('t.date_to', date('Y-m-d'))
                    ->and
                    ->nest()
                        ->addPredicate($where)
                    ->unnest();
        }

        return $collection;
    }

    /**
     * @return \Orders\Admin\Service\ProposalService
     */
    protected function getProposalService()
    {
        return $this->getServiceManager()->get('Orders\Admin\Service\ProposalService');
    }

    /**
     * @return \Orders\Admin\Service\GCalendarService
     */
    protected function getGCalendarService()
    {
        return $this->getServiceManager()->get('Orders\Admin\Service\GCalendarService');
    }

    /**
     * @return \Orders\Admin\Service\CalcService
     */
    protected function getCalcService()
    {
        return $this->getServiceManager()->get('Orders\Admin\Service\CalcService');
    }
}