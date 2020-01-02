<?php

namespace Transports\Admin\Service;

use Pipe\DateTime\Time;
use Pipe\Mvc\Service\Admin\TableService;
use Transports\Admin\Model\Transfer;
use Transports\Admin\Model\TransfersTransports;
use Transports\Admin\Model\Transport;

class TransportsService extends TableService
{
    const ERROR_NOT_FOUND = 201;
    const ERROR_DURATION  = 202;
    const ERROR_CAPACITY  = 203;
    const ERROR_PRICE     = 204;
    const ERROR_TIME      = 205;

    public function calcPriceAuto($data)
    {
        $result = [
            'errors'    => [],
            'name'      => [],
            'id'        => 0,
            'order'     => [],
        ];

        $persons = $data['count'];

        $transport = new Transport();
        $transport->select()->where
            ->greaterThanOrEqualTo('capacity', $persons);

        $duration = Time::getDT($data['duration']);
        $delivery = Time::getDT($data['car_delivery_time']);

        $fullDuration = (clone $duration)->addition($delivery);

        $timeFrom = Time::getDT($data['timeFrom']);
        $timeFrom = $timeFrom->modify('-30 minutes');

        $rPrice = $transport->getPrice([
            'delivery' => $delivery,
            'duration' => $fullDuration,
            'time'     => $timeFrom,
            'count'    => $data['count'],
        ]);

        $result['errors'] += $rPrice['errors'];

        if($result['errors']) return $result;

        $price = $rPrice['price'];

        $result['id'] = $transport->id();
        $result['name']['default'] = $transport->get('name');
        $result['name']['plural']  = $transport->get('plural_form');
        $result['name']['desc']    = $rPrice['desc'];

        $result['income'] = $price;

        return $result;
    }

    public function calcPriceManual($options)
    {
        $result = [
            'errors'    => [],
            'name'      => '',
            'income'    => 0,
            'outgo'     => 0,
            'list'      => [],
        ];

        if(!$options['transport']) {
            return $result;
        }

        foreach ($options['transport'] as $row) {
            $transports = [];

            /*if($row['id'] === '') {
                continue;
            } else*/if(!$row['id']) {
                $transports = $this->getTransportByCapacity($options['count'], $row['type'], $options['transfer_id']);
            } else {
                if(!($trType = $row['type'])) {
                    $trType = (new Transport(['id' => $row['id']]))->get('type');
                }

                $transports[] = [
                    'id'          => $row['id'],
                    'type'        => $trType,
                    'count'       => $row['count'] ?? $options['count'],
                    'income'      => $row['income'],
                    'outgo'       => $row['outgo'],
                    'transfer_id' => $options['transfer_id'],
                ];
            }

            foreach ($transports as $transport) {
                $tId = $transport['id'];

                $tResult = $this->getTransportPrice([
                    'id'          => $tId,
                    'count'       => $transport['count'],
                    'income'      => $transport['income'],
                    'outgo'       => $transport['outgo'],
                    'time'        => $options['time'],
                    'duration'    => $row['duration'],
                    'driver_id'   => $row['driver_id'],
                    'transfer_id' => $transport['transfer_id'],
                    'car_delivery_time'  => $options['car_delivery_time'],
                ]);

                $result['income'] += $tResult['income'];
                $result['outgo'] += $tResult['outgo'];

                $tResult['driver_id']   = $row['driver_id'] ?? 0;
                $tResult['transfer_id'] = $row['transfer_id'] ?? 0;
                $tResult['type'] = $transport['type'];

                $result['list'][] = $tResult;
                $result['errors'] = $result['errors'] + $tResult['errors'];
            }
        }

        return $result;
    }

    public function getTransportByCapacity($persons, $type = Transport::TYPE_AUTO, $transferId = 0)
    {
        $result = [];
        $i = 0;
        while($persons > 0 && $i < 50) {
            $transport = new Transport();
            $transport->select()
                ->order('capacity')
                ->where
                    ->equalTo('type', $type)
                    ->greaterThanOrEqualTo('capacity', $persons);


            if($transferId) {
                $transport->select()
                    ->join(['ttt' => 'transports_transfers_transports'], 'ttt.transport_id = t.id', [])
                    ->where
                        ->equalTo('ttt.depend', $transferId);
            }

            if(!$transport->load()) {
                $transport = new Transport();
                $transport->select()
                    ->order('capacity DESC')
                    ->where
                        ->equalTo('type', $type);
            }

            $capacity = $transport->get('capacity');

            $result[] = [
                'id'    => $transport->id(),
                'transfer_id'  => $transferId,
                'type'  => $transport->get('type'),
                'count' => $capacity < $persons ? $capacity : $persons,
            ];

            $i++;
            $persons -= $capacity;
        }

        return $result;
    }

    protected function getTransportPrice($data)
    {
        $result = ['errors' => []];

        $duration = Time::getDT($data['duration']);
        $timeFrom = Time::getDT($data['time']);

        $transport = new Transport();
        $transport->id($data['id']);

        if(!$transport->load()) {
            $result['errors'][self::ERROR_NOT_FOUND] = 'Транспорт не найден';
            return $result;
        }

        $result = [
            'id'        => $transport->id(),
            'name'      => '<a target="_blank" href="' . $transport->getUrl() . '">Транспорт: ' . $transport->get('name') . '</a>',
            'errors'    => [],
            'duration'  => '0000-00-00',
            'count'     => $data['count']
        ];

        if($data['transfer_id'] && $duration->isEmpty()) {
            $transfer = new Transfer(['id' => $data['transfer_id']]);

            $transferPrice = new TransfersTransports();
            $transferPrice->select()
                ->where([
                    'transport_id' => $transport->id(),
                    'depend'       => $data['transfer_id'],
                ]);

            if(!$transferPrice->load() || !$transferPrice->get('income')) {
                $result['errors'][self::ERROR_PRICE] = 'Стоимость трансфера не найдена';
                return $result;
            }

            $result = array_merge($result, [
                'income'    => $transferPrice->get('income'),
                'outgo'     => $transferPrice->get('outgo') ?? $transferPrice->get('income'),
                'desc'      => 'трансфер в ' . $transferPrice->plugin('transfer')->get('name'),
                'duration'  => $transfer->get('duration'),
            ]);

            return $result;
        }

        if($duration->isEmpty()) {
            $result['errors'][self::ERROR_TIME] = $transport->get('name') . ': не указана длительность аренды';
            return $result;
        }

        $result = [
            'id'        => $transport->id(),
            'name'      => '<a target="_blank" href="' . $transport->getUrl() . '">Транспорт: ' . $transport->get('name') . '</a>',
            'errors'    => [],
            'duration'  => $duration->format(),
            'count'     => $data['count']
        ];

        if($data['income'] || $data['outgo']) {
            $result = array_merge($result, [
                'income'    => $data['income'],
                'outgo'     => $data['outgo'],
                'desc'      => 'Фиксированная стоимость',
            ]);

            return $result;
        }

        $fullDuration = (clone $duration);
        if($transport->get('type') == Transport::TYPE_AUTO) {
            $delivery = Time::getDT($data['car_delivery_time']);
            $fullDuration->addition($delivery);
            $timeFrom = $timeFrom->subtraction($delivery->divide(2));
        }

        $rPrice = $transport->getPrice([
            'duration'  => $fullDuration,
            'delivery'  => $delivery,
            'time'      => $timeFrom,
            'count'     => $data['count'],
            'driver_id' => $data['driver_id'],
        ]);

        $result['errors'] += $rPrice['errors'];

        if($result['errors']) return $result;

        $result = array_merge($result, [
            'income'    => $rPrice['income'],
            'outgo'     => $rPrice['outgo'],
            'desc'      => $rPrice['desc'],
        ]);

        return $result;
    }
}