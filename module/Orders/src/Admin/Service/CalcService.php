<?php

namespace Orders\Admin\Service;

use Application\Admin\Model\Currency;
use Application\Admin\Model\Nationality;
use Application\Admin\Model\Settings;
use Orders\Admin\Model\OrderConstants;
use Pipe\DateTime\Time;
use Pipe\Mvc\Service\AbstractService;
use Pipe\String\Numbers;
use Excursions\Admin\Model\ExcursionDay;
use Excursions\Admin\Model\ExcursionMargin;
use Hotels\Admin\Model\Hotel;
use Hotels\Admin\Service\HotelsService;
use Museums\Admin\Model\Museum;
use Museums\Admin\Service\MuseumsService;
use Orders\Admin\Model\Order;
use Orders\Admin\Model\OrderDay;
use Orders\Admin\Model\OrderDayExtra;
use Orders\Admin\Model\OrderDayGuides;
use Orders\Admin\Model\OrderDayMuseums;
use Orders\Admin\Model\OrderDayPricetable;
use Orders\Admin\Model\OrderDayTimetable;
use Orders\Admin\Model\OrderDayTransport;
use Orders\Admin\Model\OrderHotelsRooms;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use Transports\Admin\Model\Transport;

class CalcService extends AbstractService
{
    const ERROR_DATES  	  = 1;
    const ERROR_TOURISTS  = 2;

    public function addOrder($data, $fix = false)
    {
        $calc = $this->calc($data);

        $order = new Order();
        $order->getDbAdapter()->getDriver()->getConnection()->beginTransaction();

        $orderDay = false;

        $update = false;
        if($data['order_id'] && $data['day_id']) {
            $order->id($data['order_id']);

            $orderDay = new OrderDay();
            $orderDay->id($data['day_id']);

            if(!$order->load() || !$orderDay->load()) {
                throw new \Exception('Order or OrderDay not found');
            }

            $update = true;

            $orderDay->plugin('guides')->remove();
            $orderDay->plugin('museums')->remove();
            $orderDay->plugin('transports')->remove();
            $orderDay->plugin('extra')->remove();
            $orderDay->plugin('timetable')->remove();
            $orderDay->plugin('pricetable')->remove();
            $orderDay->plugin('attrs')->remove();
        } else {
            $order->setVariables([
                'adults'   => $data['adults'],
                'children' => $data['children'],
                'status'   => OrderConstants::STATUS_PROCESS,
                'lang_id'  => $data['lang_id'],
                'agency'   => $data['agency'],
                'options'  => [
                    'color'     => array_rand(OrderConstants::$colors),
                    'currency'  => [
                        'currency' => $data['currency'],
                        'rate'     => '',
                    ],
                    'proposal'  => [
                        'lang'       => $data['kp_lang'],
                        'autocalc'   => 1,
                        'generalize' => 1
                    ],
                    'hotels'    => [
                        'days_count' => $calc['hotels']['days_count']
                    ],
                ],
            ]);

            $order->save();
            $orderId = $order->id();

            foreach ($calc['hotels']['hotels'] as $hotelData) {
                $hotelId = $hotelData['id'];

                foreach ($hotelData['rooms'] as $roomData) {
                    $orderHotels = new OrderHotelsRooms();
                    $orderHotels->setVariables([
                        'depend'    => $order->id(),
                        'hotel_id'  => $hotelId,
                        'room_id'   => $roomData['id'],
                        'tourists'  => $roomData['tourists'],
                        'breakfast' => $roomData['breakfast'],
                        'bed_size'  => $roomData['bed_size'],
                    ])->save();
                }
            }
        }

        $orderName = '';

        foreach($calc['days'] as $dayData) {
            if(!$day = $orderDay) {
                $day = new OrderDay();
            }

            $day->setVariables([
                'depend'         => $orderId,
                'day_id'         => $dayData['day_id'],
                'date'           => $dayData['date'],
                'margin'         => $dayData['margin'],
                'time'           => $dayData['time'],
                'transfer_time'  => $dayData['transfer_time'],
                'transfer_id'    => $dayData['transfer_id'],
                'car_delivery_time'  => $dayData['car_delivery_time'],
                'adults'         => $dayData['adults'],
                'children'       => $dayData['children'],
                'duration'       => $dayData['duration'],
                //'margin'         => $dayData['summary']['percent'],
                'options'   => [
                    'extra'      => ['autocalc' => 1],
                    'museums'    => ['autocalc' => 1],
                    'guides'     => ['autocalc' => 1],
                    'transports' => ['autocalc' => 1],
                    'proposal'   => [
                        'place_start' => $dayData['proposal']['place_start'],
                        'place_end'   => $dayData['proposal']['place_end'],
                        'timetable'   => [
                            'autocalc'   => $dayData['proposal']['timetable']['autocalc'],
                        ],
                        'pricetable'  => [
                            'autocalc'   => $dayData['proposal']['pricetable']['autocalc'],
                        ],
                    ],
                ],
            ]);
            $day->save();
            $dayId = $day->id();

            foreach ($dayData['proposal']['timetable']['list'] as $row) {
                $oTimetable = new OrderDayTimetable();
                $oTimetable->setVariables([
                    'depend'        => $dayId,
                    'name'          => $row['name'],
                    'duration'      => $row['duration'],
                    'sort'          => $row['sort'],
                ])->save();
            }

            foreach ($dayData['proposal']['pricetable']['list'] as $row) {
                $oTimetable = new OrderDayPricetable();
                $oTimetable->setVariables([
                    'depend'        => $dayId,
                    'name'          => $row,
                ])->save();
            }

            foreach ($dayData['extra']['list'] as $row) {
                $oTimetable = new OrderDayExtra();
                $oTimetable->setVariables([
                    'depend'        => $dayId,
                    'name'          => $row['name'],
                    'proposal_name' => $row['proposal_name'],
                    'income'        => $row['income'],
                    'outgo'         => $row['outgo'],
                    'sort'          => $row['sort'],
                ])->save();
            }

            foreach ($dayData['museums']['list'] as $museum) {
                $museumObj = new Museum();
                $museumObj->id($museum['id']);

                if(!$dayData['excursion_id']) {
                    $orderName .= ($orderName ? ', ' : '') . $museumObj->get('name');
                }

                $extra = 0;
                foreach ($museum['extra'] as $extraR) {
                    $extra += $extraR['income'];
                }

                $oTicket = new OrderDayMuseums();
                $oTicket->setVariables([
                    'depend'            => $dayId,
                    'museum_id'         => $museum['museum_id'],
                    'duration'          => $museum['duration'],
                ]);

                if($fix) {
                    $oTicket->setVariables([
                        'tickets_adults'    => $museum['tickets_adults'],
                        'tickets_children'  => $museum['tickets_children'],
                        'guides'            => $museum['guides'],
                        'extra'             => $museum['extra'],
                        'outgo'             => $museum['outgo'],
                    ]);
                }

                $oTicket->save();
            }

            foreach ($dayData['transports']['list'] as $transport) {
                $oTransport = new OrderDayTransport();
                $oTransport->setVariables([
                    'depend'        => $dayId,
                    'transport_id'  => $transport['id'],
                    'duration'      => $transport['duration'],
                    'passengers'    => $transport['count'],
                    'paid'          => 0,
                ]);

                if($fix) {
                    $oTransport->setVariables([
                        'income'    => $transport['income'],
                        'outgo'     => $transport['outgo'],
                    ]);
                }

                $oTransport->save();
            }

            foreach($dayData['guides']['list'] as $guide) {
                $oGuides = new OrderDayGuides();
                $oGuides->setVariables([
                    'depend'        => $dayId,
                    'guide_id'      => 0,
                    'duration'      => $guide['duration'],
                    'paid'          => 0,
                    'payment_type'  => 0,
                ])->save();

                if($fix) {
                    $oGuides->setVariables([
                        'income'    => $guide['income'],
                        'outgo'     => $guide['outgo'],
                    ]);
                }

                $oGuides->save();
            }
        }

        if($update) {
            $order = new Order();
            $order->id($data['order_id'])->load();

            if($order->get('options')->proposal->autocalc) {
                $html = $this->getOrderService()->getOrderProposalHtml($order);
                $order->set('proposal', $html);
                $order->save();
            }
        } else {
            $order->set('name', $orderName);
            $order->set('proposal', $this->getProposalService()->getProposalHtml($this->calc($data)));
        }


        /** @var OrdersService $ordersService */

        $order->getDbAdapter()->getDriver()->getConnection()->commit();

        $ordersService = $this->getServiceManager()->get('Orders\Admin\Service\OrdersService');
        $ordersService->saveModelAfter($order);

        return $order;
    }

    public function roundPrice($price)
    {
        return round($price / 10) * 10;
    }

    public function calc($data)
    {
        $commonData = [
            'adults'     => (int) $data['adults'],
            'children'   => $data['children'] ? (int) $data['children'] : 0,
            'tourists'   => $data['adults'] + $data['children'],
            'lang_id'    => $data['lang_id'],
            'currency'   => [
                'currency'  => $data['currency'] ?? 'rub',
                'rate'      => $data['currency_rate']
            ],
            'agency'     => $data['agency'],
            'margin'     => $data['margin'],
            'foreigners' => ([$data['lang_id'] != 1, 3]),
            'calc_type'  => ($data['calc_type'] ?? 'calc'),
        ];
        //unset($commonData['days']);

        $currencies = ['rub'];
        switch ($commonData['currency']['currency']) {
            case 'rub':
                break;
            case 'all':
                $currencies[] = 'eur';
                $currencies[] = 'usd';
                break;
            default:
                $currencies[] = $commonData['currency']['currency'];
        }
        $commonData['currencies'] = $currencies;

        $result = [
            'days'       => [],
            'clients'    => [],
            'summary'    => [],
            'errors'     => [],
            'notices'    => [],
            'adults'     => $data['adults'],
            'children'   => $data['children'],
            'lang_id'    => $data['lang_id'],
            'kp_lang'    => $data['kp_lang'] ?? 'ru',
            'margin'     => $data['margin'],
            'manager_id' => $data['manager_id'],
            'calc_type'  => $commonData['calc_type'],
            'currency'   => $commonData['currency'],
        ];

        if(!$result['currency']['rate']) {
            $result['currency']['rate'] = (new Currency(['currency' => $result['currency']['currency']]))->getRate();
        }

        if($data['clients']) {
            $result['clients'] = $data['clients'];
        }

        if(empty($data['adults']) && empty($data['children'])) {
            $result['errors'][self::ERROR_TOURISTS] = 'Не выбрано количество туристов';
        }

        if($result['errors']) {
            return $result;
        }

        //Days
        $dates = [];
        $margin = 0;
        foreach ($data['days'] as $dayId => $day) {
            if(in_array($day['date'], $dates)) {
                $result['notices'][self::ERROR_DATES] = 'Несколько дней попадают на одну дату';
            }

            $dates[] = $day['date'];
            $resData = $this->calcDay($day, $commonData);

            $result['days'][$dayId] = $resData;

            foreach ($resData['summary'] as $currency => $daySum) {
                $sum = &$result['summary'];
                $sum[$currency]['adult']   += $daySum['adult'];
                $sum[$currency]['child']   += $daySum['child'];
                $sum[$currency]['outgo']   += $daySum['outgo'];
                $sum[$currency]['income']  += $daySum['income'];
            }

            $margin += $resData['margin'];
        }
        $margin /= count($data['days']);

        //Hotels
        if($data['hotels']['hotels']) {
            $hotelsResult = $this->calcHotels($data['hotels'], $commonData, $dates, $margin);

            foreach($commonData['currencies'] as $currency) {
                $sum = &$result['summary'][$currency];
                $hotelSum = $hotelsResult['summary'][$currency];

                $sum['income'] += $hotelSum['income'];
                $sum['outgo']  += $hotelSum['outgo'];
                $sum['adult']  += $hotelSum['adult'];
                $sum['child']  += $hotelSum['child'];
            }

            $result['hotels'] = $hotelsResult;
        } else {
            $result['hotels'] = ['hotels' => []];
        }

        foreach($commonData['currencies'] as $currency) {
            $currencyMdl = new Currency(['currency' => $currency, 'rate' => $commonData['currency']['rate']]);

            $sum = &$result['summary'][$currency];
            $sum['percent'] = round(($sum['income'] * $currencyMdl->getRate() / $result['summary']['rub']['outgo'] - 1) * 100);
        }

        if($data['summary']) {
            $result['summary'] = $data['summary'];
        }

        return $result;
    }

    public function calcHotels($data, $commonData, $dates, $margin)
    {
        $hotelsResult = [
            'hotels'  => [],
            'notices' => [],
        ];

        if($data['days_count']) {
            $daysCount = $data['days_count'];
        } else {
            $daysCount = max(1, count($dates) - 1);
        }

        $hotelsResult['days_count'] = $daysCount;
        $totalIncome   = 0;
        $totalOutgo    = 0;
        $touristPrice  = 0;

        $touristsTaken = 0;
        foreach ($data['hotels'] as $hotelData) {
            $hotel = new Hotel(['id' => $hotelData['id']]);
            $roomsPrice = $this->getHotelsService()->calcHotel([
                'hotel'    => $hotel,
                'rooms'    => $hotelData['rooms'],
                'date'     => $dates[0],
            ]);

            $hotelResult = [
                'id'     => $hotel->id(),
                'name'   => $hotel->get('name'),
            ] + $roomsPrice;

            foreach ($hotelData['rooms'] as $roomData) {
                $touristsTaken += $roomData['tourists'];
            }

            $hotelResult['income'] *= $daysCount;
            $hotelResult['outgo']  *= $daysCount;

            $hotelResult['desc'] =
                $hotelResult['income'] . ' (' .
                    $roomsPrice['income'] . ' * ' . Numbers::declensionRu($daysCount, ['день', 'дня', 'дней'])
                . ')';

            $hotelsResult['hotels'][] = $hotelResult;
            $totalIncome   += $hotelResult['income'];
            $totalOutgo    += $hotelResult['outgo'];
            $touristPrice  += $hotelResult['adult'];
        }

        if($touristsTaken != 0 && $touristsTaken != ($commonData['adults'] + $commonData['children'])) {
            $hotelsResult['notices'][HotelsService::NOTICE_HOTELS_CAPACITY] = 'Кол-во номеров не соответствует кол-ву туристов';
        }

        $summary = [];
        $touristPrice = $totalIncome / $touristsTaken;
        foreach ($commonData['currencies'] as $currencyCode) {
            $currency = new Currency(['currency' => $currencyCode, 'rate' => $commonData['currency']['rate']]);
            $summary[$currencyCode] = [
                'adult'    => $currency->getPrice($touristPrice),
                'child'    => $currency->getPrice($touristPrice),
                'income'   => $currency->getPrice($totalIncome),
                'outgo'    => $totalOutgo,
                'percent'  => round((($currency->getPrice($totalIncome) * $currency->getRate()) / $totalOutgo - 1) * 100),
            ];
        }

        $hotelsResult['tourists'] = $touristsTaken;
        $hotelsResult['summary'] = $summary;

        return $hotelsResult;
    }

    public function calcDay($dayData, $commonData)
    {
        $fullData = $dayData + $commonData;

        $exDay = $dayData['day_id'] ? (new ExcursionDay())->id($dayData['day_id'])->load() : false;

        $adults = $commonData['adults'];
        $children = $commonData['children'];
        $tourists = $adults + $children;

        $result['day_id'] = $dayData['day_id'];
        $result['transfer_id'] = $dayData['transfer_id'];
        $result['time']   = $dayData['time'];

        //Transport
        $result['transports'] = $this->getTransportsService()->calcPriceManual([
            'car_delivery_time' => $dayData['car_delivery_time'],
            'transport'   => $dayData['transports'],
            'transfer_id' => $dayData['transfer_id'],
            'time'        => $dayData['time'],
            'count'       => $tourists
        ]);

        $isWalking = true;
        if(!$result['transports']) {
            $result['transports']['errors'] = [];
            $result['transports']['list'] = [];
        } else {
            foreach ($result['transports']['list'] as $row) {
                if($row['type'] == Transport::TYPE_AUTO) $isWalking = false;
            }
        }

        //Museums
        $museumsDt = Time::getDT();
        if(!empty($dayData['museums'])) {
            foreach ($dayData['museums'] as $museumData) {
                if(!$museumData['duration']) {
                    $result['errors'][MuseumsService::ERROR_TIME] = 'Не указана длительность экскурсии';
                    continue;
                }
                $museumsDt = $museumsDt->addition($museumData['duration']);
                //$museumsDuration += $museumData['duration'];
            }

            if(empty($result['errors'])) {
                $result['museums'] = $this->museum($fullData + ['isWalking' => $isWalking]);
            }
        } else {
            $result['museums']['errors'] = [];
            $result['museums']['list'] = [];
        }

        $durationDt = Time::getDT();
        if(isset($dayData['proposal']['timetable']['list'])) {
            $i = 0;
            foreach ($dayData['proposal']['timetable']['list'] as $row) {
                $i++;
                if ($i == 1 && $row['duration'] == '00:15:00') continue;
                $durationDt->addition($row['duration']);
            }
        }

        $fullData['duration'] = $durationDt;
        $result['duration'] = $durationDt->format();

        $result['car_delivery_time'] = $dayData['car_delivery_time'];
        $result['transfer_time'] = Time::getDT($dayData['transfer_time'])->format();

        //Guides
        $result['guides'] = $this->guides($fullData, $dayData, $exDay);

        //Extra
        $result['extra'] = $this->getExcursionsService()->getExtraList($dayData, $commonData);

        $result['proposal'] = [
            'place_start' => $dayData['proposal']['place_start'],
            'place_end'   => $dayData['proposal']['place_end'],
        ];

        $result['proposal']['timetable'] = $dayData['proposal']['timetable'];
        $result['proposal']['pricetable'] = $dayData['proposal']['pricetable'];

        $result['proposal']['pricetable']['list'] = $this->getProposalService()->getPriceTable($result, $commonData);
        $result['proposal']['timetable']['list'] = $this->getProposalService()->getTimeTable($result, $commonData, $exDay);

        //guides
        $outgo = $adult = $child = 0;

        $outgo += $result['guides'] ? $result['guides']['outgo'] : 0;
        $adult += round($result['guides'] ? $result['guides']['income'] / $tourists: 0);
        $child += round($children && $result['guides'] ? $result['guides']['income'] / $tourists: 0);

        //transport
        $outgo += $result['transports'] ? $result['transports']['outgo'] : 0;
        $adult += round($result['transports'] ? $result['transports']['income'] / $tourists: 0);
        $child += round($children && $result['transports'] ? $result['transports']['income'] / $tourists: 0);

        //extra
        $outgo += $result['extra'] ? $result['extra']['outgo'] : 0;
        $adult += round($result['extra'] ? $result['extra']['income'] / $tourists: 0);
        $child += round($children && $result['extra'] ? $result['extra']['income'] / $tourists: 0);

        if($result['museums']['list']) {
            foreach ($result['museums']['list'] as $museum) {
                $outgo += $museum['income'];
                $adult += $museum['adult'];
                $child += $children ? $museum['child'] : 0;
            }
        }

        //Ruble
        if($exDay) {
            $margin = $this->calcMargin($commonData, $dayData, $exDay->plugin('excursion'));
        } else {
            $margin = $this->calcMargin($commonData, $dayData);
        }
        //Summary
        $summary = [];

        foreach($commonData['currencies'] as $currency) {
            $summary[$currency] = $this->daySummary([
                'currency'  => $currency,
                'currency_rate'  => $commonData['currency']['rate'],
                'adult'     => $adult,
                'child'     => $child,
                'adults'    => $adults,
                'children'  => $children,
                'outgo'     => $outgo,
                'margin'    => $margin,
            ]);
        }

        $result['margin']   = $dayData['margin'];
        $result['summary']  = $summary;
        $result['date']     = $dayData['date'];
        $result['time']     = $dayData['time'];
        //$result['transport_type'] = $dayData['transport_type'];

        if($exDay) {
            $result['day_id'] = $exDay->id();
        }

        return $result;
    }

    protected function daySummary($data) {
        $currency = new Currency(['currency' => $data['currency'], 'rate' => $data['currency_rate']]);

        $adult = $currency->getPrice($data['adult'] * $data['margin']);
        $child = $currency->getPrice($data['child'] * $data['margin']);

        $income = ($adult * $data['adults']) + ($child * $data['children']);
        $outgo  = $data['outgo'];

        $result = [
            'adult'   => $adult,
            'child'   => $child,
            'income'  => $income,
            'outgo'   => $currency->getPrice($outgo),
        ];

        if($income) {
            $result['percent'] = round((($income * $currency->getRate()) / $outgo - 1) * 100);
        }

        return $result;
    }

    public function calcMargin($commonData, $dayData, $excursion = null)
    {
        $tourists = $commonData['adults'] + $commonData['children'];
        $cMargin = 1;
        $aMargin = 1;

        $settings = Settings::getInstance();
        if($commonData['agency']) {
            $aMargin = 1 + ($settings->get('margin')->agency / 100);
        }

        //Custom margin
        if(intval($dayData['margin']) >= 0) {
            return  round((1 + ($dayData['margin'] / 100)) * $aMargin, 2);
        }

        //Default margin
        $cMargin *= 1 + ($settings->get('margin')->client / 100);

        if(!$excursion) {
            return round($cMargin * $aMargin, 2);
        }

        //Excursion margin
        $exMargin = new ExcursionMargin();
        $exMargin->select()
            ->order('tourists DESC')
            ->where
                ->equalTo('depend', $excursion->id())
                ->lessThanOrEqualTo('tourists', $tourists);

        if($exMargin->load()) {
            $cMargin = 1 + ($exMargin->get('margin') / 100);
        }

        return round($cMargin * $aMargin, 2);
    }

    protected function guides($fullData, $dayData, $exDay)
    {
        if($fullData['guides']['autocalc'] === '0') {
            return $this->getGuidesService()->calcPriceManual([
                'list'    => $fullData['guides']['list'],
                'time'    => $fullData['time'],
                'lang_id' => $fullData['lang_id'],
                'transfer_id' => $dayData['transfer_id'],
            ]);
        }

        if($exDay) {
            $excGuides = $exDay->plugin('guides', ['foreigners' => Nationality::langToNationality($fullData['lang_id']), 'tourists' => $fullData['tourists']]);
            if ($excGuides->count()) {
                $excGuide = $excGuides->rewind()->current();
            } else {
                $excGuide = null;
            }
        }

        $guidesCount = 1;
        if($dayData['guides-calc']['count']) {
            $guidesCount = $dayData['guides-calc']['count'];
        } elseif($exDay) {
            if ($excGuide) {
                $guidesCount = $excGuide->get('guides');
            }
        }

        /*if($dayData['guides-calc']['transfer_id'] == 'auto' && $excGuide) {
            $transferId = $excGuide->get('transfer_id');
        } else {
            $transferId = (int) $dayData['guides-calc']['transfer_id'];
        }*/

        $duration = $fullData['duration'];
        if($dayData['guides-calc']['duration']) {
            $duration = $dayData['guides-calc']['duration'];
        }

        return $this->getGuidesService()->calcPriceAuto([
            'duration'     => $duration,
            'lang_id'      => $fullData['lang_id'],
            'transfer_id'  => $dayData['transfer_id'],
            'guidesCount'  => $guidesCount,
            'time'         => $dayData['time'],
        ]);
    }

    protected function transportAuto($data, $duration)
    {
        return $this->getTransportsService()->calcPriceAuto([
            'duration'  => $duration,
            'count'     => $data['adults'] + $data['children'],
            'timeFrom'  => $data['time'],
        ]);
    }

    protected function museum($data)
    {
        $result = [
            'list'   => [],
            'errors' => [],
        ];
        $timeFrom = Time::getDT($data['time']);

        $transferTime = Time::getDT($data['transfer_time']);
        if($transferTime->format('H:i') > '00:15') {
            $timeFrom->addition($transferTime);
        }

        $timeTo = clone $timeFrom;

        //Уникальные доп. доходы
        $uniqueExtra = ['Наушники' => 0];

        foreach ($data['museums'] as $museumData) {
            $timeTo->addition($museumData['duration']);

            $museum = new Museum();
            $museum->id($museumData['id'])->load();

            if(empty($museumData['duration'])) {
                $res['errors'][MuseumsService::ERROR_TIME] = 'Не выбрана длительность экскурсии';
                continue;
            }

            $mResult = $this->getMuseumsService()->calcPrice($museum, [
                'date'      => $data['date'],
                'isWalking' => $data['isWalking'],
                'duration'  => $museumData['duration'],
                'adults'    => $data['adults'],
                'children'  => $data['children'],
                'timeFrom'  => $timeFrom,
                'timeTo'    => $timeTo,
                'lang_id'   => $data['lang_id'],
                //'transport_type'   => $data['transport_type'],
                'unique_extra'     => &$uniqueExtra,
                'tickets'          => $museumData['tickets'],
                'tickets_adults'   => $museumData['tickets_adults'],
                'tickets_children' => $museumData['tickets_children'],
                'guides'    => $museumData['guides'],
                'extra'     => $museumData['extra'],
                'outgo'     => $museumData['outgo'],
            ]);

            $result['list'][] = $mResult;
            $result['errors'] += $mResult['errors'];
            $timeFrom = (clone $timeTo);
        }

        return $result;
    }

    public function word($text, $file = false)
    {
        $text = str_replace(["\n", "\r", "\t"], '', $text);
        $text = str_replace(['h1', 'h2', 'h3', 'h4'], 'b', $text);

        $word = new PhpWord();
        $section = $word->addSection();
        Html::addHtml($section, $text);
        $writer = IOFactory::createWriter($word, 'Word2007');

        if(!$file) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment;filename="proposal.docx"');
            $writer->save('php://output');
            die();
        }

        $file = DATA_DIR . '/print.docx';
        $writer->save($file);
        return $file;
    }

    /**
     * @return \Museums\Admin\Service\MuseumsService
     */
    protected function getOrderService()
    {
        return $this->getServiceManager()->get('Orders\Admin\Service\OrdersService');
    }

    /**
     * @return \Museums\Admin\Service\MuseumsService
     */
    protected function getMuseumsService()
    {
        return $this->getServiceManager()->get('Museums\Admin\Service\MuseumsService');
    }

    /**
     * @return \Transports\Admin\Service\TransportsService
     */
    protected function getTransportsService()
    {
        return $this->getServiceManager()->get('Transports\Admin\Service\TransportsService');
    }

    /**
     * @return \Guides\Admin\Service\GuidesService
     */
    protected function getGuidesService()
    {
        return $this->getServiceManager()->get('Guides\Admin\Service\GuidesService');
    }

    /**
     * @return \Hotels\Service\HotelsService
     */
    protected function getHotelsService()
    {
        return $this->getServiceManager()->get('Hotels\Admin\Service\HotelsService');
    }

    /**
     * @return \Orders\Admin\Service\ProposalService
     */
    protected function getProposalService()
    {
        return $this->getServiceManager()->get('Orders\Admin\Service\ProposalService');
    }

    /**
     * @return \Excursions\Admin\Service\ExcursionsService
     */
    protected function getExcursionsService()
    {
        return $this->getServiceManager()->get('Excursions\Admin\Service\ExcursionsService');
    }
}