<?php

namespace Museums\Admin\Service;

use Application\Admin\Model\Age;
use Application\Admin\Model\Nationality;
use Pipe\DateTime\Date;
use Pipe\DateTime\Time;
use Pipe\Mvc\Service\Admin\TableService;
use Museums\Admin\Model\MuseumExtra;
use Museums\Admin\Model\MuseumGuide;
use Museums\Admin\Model\MuseumTickets;
use Museums\Admin\Model\MuseumWeekends;
use Museums\Admin\Model\MuseumWorktime;
use Orders\Admin\Model\Order;

class MuseumsService extends TableService
{
    const ERROR_TICKETS   = 101;
    const ERROR_WEEKEND   = 102;
    const ERROR_WORKDAY   = 103;
    const ERROR_WORKTIME  = 104;
    const ERROR_TIME      = 105;
    const ERROR_NOT_FOUND = 106;

    public function calcPrice($museum, $opts)
    {
        $adults = (int) ($opts['adults']);
        $children = (int) ($opts['children']);
        $tourists = $adults + $children;

        $nationality = Nationality::langToNationality($opts['lang_id']);

        $timeFromDt = Time::getDT($opts['timeFrom']);
        $timeToDt = Time::getDT($opts['timeTo']);

        $result = [
            'museum_id' => $museum->id(),
            'name'      => '<a target="_blank" href="' . $museum->getUrl() . '">' . $museum->get('name') . '</a>',
            'time_from' => $timeFromDt->format('H:i:s'),
            'time_to'   => $timeToDt->format('H:i:s'),
            'income'    => 0,
            'outgo'     => 0,
            'adult'     => 0,
            'child'     => 0,
            'adults_count'    => $adults,
            'children_count'  => $children,
            'duration'  => $opts['duration'],
            'tickets_adults'   => 0,
            'tickets_children' => 0,
            'guides'    => 0,
            'extra'     => [],
            'errors'    => [],
        ];

        if(!$museum->load()) {
            $result['errors'][self::ERROR_NOT_FOUND] = 'Музей не найден';
            return $result;
        }

        if($opts['date']) {
            try {
                $dt = Date::getDT( $opts['date']);
            } catch (\Exception $e) {
                $dt = Date::getDT('NOW');
            }
        } else {
            $dt = Date::getDT('NOW');
        }

        $workdays = new MuseumWorktime();
        $workdays->select()
            ->where
                ->equalTo('depend', $museum->id())
                ->equalTo('weekday', $dt->format('N'));

        if(!$workdays->load()) {
            $result['errors'][self::ERROR_WORKDAY] = 'Выходной день в музее';
            return $result;
        }

        if (
            $timeFromDt->format('H:i:s') < $workdays->get('time_from') ||
            $timeToDt->format('H:i:s')   < $workdays->get('time_from') ||
            $timeToDt->format('H:i:s')   > $workdays->get('time_to')
        ) {
            $result['errors'][self::ERROR_WORKTIME] =
                'Выход за пределы рабочего времени (с ' .
                \DateTime::createFromFormat('H:i:s', $workdays->get('time_from'))->format('H:i')
                . ' до ' .
                \DateTime::createFromFormat('H:i:s', $workdays->get('time_to'))->format('H:i') . ')';
        }

        $weekends = new MuseumWeekends();
        $weekends->select()->where
            ->equalTo('depend', $museum->id())
            ->lessThanOrEqualTo('date_from', $dt->format('0000-m-d'))
            ->greaterThanOrEqualTo('date_to', $dt->format('0000-m-d'));

        if($weekends->load()) {
            $result['errors'][self::ERROR_WEEKEND] = 'Заказ попадает на праздничный день';
        }

        if($opts['tickets_adults'] || $opts['tickets_children'] || $opts['guides'] || $opts['extra'] || $opts['outgo']) {
            $income = $opts['tickets_adults'] + $opts['tickets_children'] + $opts['guides'] + $opts['extra'];

            @$adultTickets = (int) ($opts['tickets_adults'] / $adults);
            @$childTickets = (int) ($opts['tickets_children'] / $children);
            $adultPrice = $childPrice = (int) ($income / $tourists);

            $result['income']           = $income;
            $result['tickets_adults']   = $opts['tickets_adults'];
            $result['tickets_children'] = $opts['tickets_children'];
            $result['tickets']          = $opts['tickets_adults'] + $opts['tickets_children'];
            $result['guides']           = $opts['guides'];
            $result['adult']            = $adultPrice;
            $result['adult_tickets']    = $adultTickets;
            $result['child']            = $childPrice;
            $result['child_tickets']    = $childTickets;

            $extraArr[] = [
                'income' => (int) $opts['extra'],
                'name'   => 'Доп. расходы',
                'proposal_name'  => '',
                'desc'  => $opts['extra'] . ' руб.',
            ];
            $result['extra'] = $extraArr;

            return $result;
        }

        //tickets
        $tickets = new MuseumTickets();
        if(!$tickets->loadByDate($museum->id(), $dt, $nationality)) {
            $result['errors'][self::ERROR_TICKETS] = 'Не найдено цен на билеты';
            return $result;
        }

        $ticketsAdults   = $adults * $tickets->get('adult_price');
        $ticketsChildren = $children * $tickets->get('child_price');
        $ticketsMin      = $tickets->get('min_price');

        if($ticketsAdults + $ticketsChildren < $ticketsMin) {
            $touristPrice    = $ticketsMin / $tourists;
            $ticketsAdults   = $touristPrice * $adults;
            $ticketsChildren = $touristPrice * $children;
        }

        //guides
        $guidesPrice = 0;

        $guides = new MuseumGuide();
        $guides->select()
            ->order('count DESC')
            ->where
            ->equalTo('depend', $museum->id())
            ->lessThanOrEqualTo('count', $tourists)
            ->nest()
                ->equalTo('foreigners', $nationality)
                ->or
                ->equalTo('foreigners', Nationality::NATIONALITY_ALL)
            ->unnest();

        if($adults) {
            $guides->select()
                ->where(['age' => [Age::AGE_ADULT, Age::AGE_ALL]]);
        } else {
            $guides->select()
                ->where(['age' => [Age::AGE_CHILDREN, Age::AGE_ALL]]);
        }

        if ($guides->load()) {
            $guidesPrice += $guides->get('price');
        }

        //extra
        $extra = MuseumExtra::getEntityCollection();
        $extra->select()
            ->where
            ->nest()
                ->nest()
                    ->equalTo('tourists_from', 0)
                    ->equalTo('tourists_to', 0)
                ->unnest()
                ->or
                ->nest()
                    ->lessThanOrEqualTo('tourists_from', $tourists)
                    ->greaterThanOrEqualTo('tourists_to', $tourists)
                ->unnest()
            ->unnest()
            ->equalTo('depend', $museum->id())
            ->nest()
            ->equalTo('foreigners', Nationality::langToNationality($opts['lang_id']))
            ->or
            ->equalTo('foreigners', Nationality::NATIONALITY_ALL)
            ->unnest()
            ->nest()
                ->equalTo('transport_type', ($opts['isWalking'] ? MuseumExtra::TRANSPORT_WALK : MuseumExtra::TRANSPORT_AUTO))
                ->or
                ->equalTo('transport_type', MuseumExtra::TRANSPORT_ALL)
            ->unnest();

        $extraArr = [];
        $extraPrice = 0;
        foreach ($extra as $item) {
            $extraName = trim($item->get('name'));

            if(array_key_exists($extraName, $opts['unique_extra'])) {
                if($opts['unique_extra'][$extraName]) {
                    $extraArr[] = [
                        'income' => 0,
                        'name'   => $extraName,
                        'proposal_name'  => $item->get('proposal_name'),
                        'desc'  => 'бесплатно',
                    ];
                    continue;
                } else {
                    $opts['unique_extra'][$extraName] = 1;
                }
            }

            if($item->get('price_type') == MuseumExtra::PRICE_GROUP) {
                $extraPrice += $item->get('income');
                $extraArr[] = [
                    'income' => $item->get('income'),
                    'name'  => $item->get('name'),
                    'proposal_name'  => $item->get('proposal_name'),
                    'desc'  => $item->get('income') . ' руб.',
                ];
            } else {
                $extraPrice += $item->get('income') * $tourists;
                $extraArr[] = [
                    'income' => $item->get('income') * $tourists,
                    'name'  => $item->get('name'),
                    'proposal_name'  => $item->get('proposal_name'),
                    'desc'  => $extraPrice . ' (' . $item->get('income') . ' * ' . $tourists . ' чел.)',
                ];
            }
        }

        //dd($ticketsAdults + $ticketsChildren + $guidesPrice + $extraPrice);
        $result['income']   = $ticketsAdults + $ticketsChildren + $guidesPrice + $extraPrice;
        $result['tickets']   = $ticketsAdults + $ticketsChildren;
        $result['tickets_adults']   = $ticketsAdults;
        $result['tickets_children'] = $ticketsChildren;
        $result['guides']  = $guidesPrice;
        $result['extra']   = $extraArr;

        if($adults) {
            $result['adult'] = (int)(($ticketsAdults / $adults) + (($guidesPrice + $extraPrice) / $tourists));
            $result['adult_tickets'] = (int)($ticketsAdults / $adults);
        }

        if($children) {
            $result['child'] = (int)(($ticketsChildren / $children) + (($guidesPrice + $extraPrice) / $tourists));
            $result['child_tickets'] = (int)($ticketsChildren / $children);
        }

        return $result;
    }
}