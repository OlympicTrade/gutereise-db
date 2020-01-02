<?php
namespace Sync\Admin\Service;

use Application\Admin\Model\Settings;
use Pipe\DateTime\Date;
use Pipe\Mvc\Service\Admin\TableService;
use Excursions\Admin\Model\Excursion;
use Transports\Admin\Model\Transport;

class PriceService extends TableService
{
    static public function syncRequest($type, $id)
    {
        $url = SYNC_DOMAIN . '/sync/sync-request/?' . http_build_query(['type' => $type, 'db_id' => $id]);
        (new \GuzzleHttp\Client(['verify' => false]))->request('GET', $url);
    }

    public function getSettingsData()
    {
        $settings = Settings::getInstance();
        $result = [
            'euro_rate'  => $settings->get('data')->euro_rate,
        ];

        return $result;
    }

    public function getTransportData($id)
    {
        $transport = new Transport();
        $transport->id($id);

        $priceList = $transport->plugin('price');
        $priceList->select()->order('price_day DESC');
        $price = $priceList->rewind()->current();

        $result = [
            'capacity'  => $transport->get('capacity'),
            'price'     => $price->get('price_day'),
            'transfer'  => $price->get('transfer'),
        ];

        return $result;
    }

    public function getExcursionData($id)
    {
        $excursion = new Excursion();
        $excursion->id($id);

        $calcData = $this->generateCalcData([
            'excursion_id'  => $id,
            'lang_id'       => 1,
            'currency'      => 'rub',
            'adults'        => 15,
            'children'      => 0,
            'date'          => '04.07.2019',
        ]);
        $calc = $this->getCalcService()->calc($calcData);

        /*$calcData = $this->generateCalcData([
            'excursion_id'  => $id,
            'lang'          => 2,
            'adults'        => 15,
            'children'      => 0,
            'date'          => '04.07.2018',
            //'time'          => $excursion->get('min_time'),
        ]);
        $priceEur = $this->getCalcService()->calc($calcData)["summary"]['euro']['income'];*/

        $result = [
            'days'      => [],
            'price'     => [
                'rub' => [
                    'total' => $price['summary']['income'],
                    'adult' => $price['summary']['adult'],
                ],
                'usd' => [
                    'total' => $price['summary']['income'],
                    'adult' => $price['summary']['adult'],
                ],
                'eur' => [
                    'total' => $price['summary']['income'],
                    'adult' => $price['summary']['adult'],
                ],
            ],
        ];
		
		foreach($calc['summary'] as $currency => $price) {
			$result['price'][$currency] = [
				'total' => $price['income'],
				'adult' => $price['adult'],
			];
		}

        foreach ($excursion->plugin('days') as $day) {
            $result['days'][] = [
                'duration'  => $day->getDuration()->format(),
                'min_time'  => $day->get('min_time'),
                'max_time'  => $day->get('max_time'),
            ];
        }

        return $result;
    }

    public function calcPrice($params)
    {
        $cData = $this->generateCalcData($params);
        $resp = $this->getCalcService()->calc($cData);

        return $resp;
    }

    public function addOrder($params)
    {
        $cData = $this->generateCalcData($params);
        $resp = $this->getCalcService()->addOrder($cData);

        return $resp;
    }

    protected function generateCalcData($params)
    {
        $excursion = new Excursion();
        $excursion->id($params['excursion_id']);

        $fullData = [
            'lang_id'    => $params['lang_id'],
            'currency'   => $params['currency'],
            'adults'     => $params['adults'],
            'children'   => $params['children'],
            'agency'     => 0,
            'days'       => []
        ];

        $date = Date::getDT($params['date']);

        $di = 0;
        foreach ($excursion->plugin('days') as $day) {
            $di++;

            $dayData = [
                'day_id'             => $day->id(),
                'date'               => $date->format('d.m.Y'),
                'time'               => $params['time'] ? $params['time'] : $day->get('min_time'),
                'transfer_time'      => $day->get('transfer_time'),
                'car_delivery_time'  => $day->get('car_delivery_time'),
                'museums'            => [],
                'transports'         => [],
            ];
            $date->modify('+1 day');

            $i = 0;
            foreach ($day->plugin('museums') as $eMuseum) {
                $i++;
                $dayData['museums'][$i]['id'] = $eMuseum->get('museum_id');
                $dayData['museums'][$i]['duration'] = $eMuseum->get('duration');
            }

            $i = 0;
            foreach ($day->plugin('transport') as $eTransport) {
                $i++;

                if(!$eTransport->get('transport_id')) {
                    $dayData['transports'][$i]['id'] = 0;
                } else {
                    $dayData['transports'][$i]['id'] = $eTransport->get('transport_id');
                }

                $dayData['transports'][$i]['type'] = $eTransport->get('type');
                $dayData['transports'][$i]['type'] = $eTransport->get('type');
                $dayData['transports'][$i]['duration'] = $eTransport->get('duration');
            }

            $fullData['days'][$di] = $dayData;
        }

        return $fullData;
    }

    /**
     * @return \Orders\Admin\Service\CalcService
     */
    protected function getCalcService()
    {
        return $this->getServiceManager()->get('Orders\Admin\Service\CalcService');
    }
}