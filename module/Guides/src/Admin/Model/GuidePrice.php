<?php
namespace Guides\Admin\Model;

use Pipe\DateTime\Date;
use Pipe\DateTime\Time;
use Pipe\Db\Entity\Entity;
use Guides\Admin\Service\GuidesService;
use Transports\Admin\Model\Transfer;
use Transports\Admin\Model\TransfersGuides;

class GuidePrice extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'guides_price',
            'properties' => [
                'lang_id'   => [],
                'price'     => [],
                'min_price' => [],
                'max_price' => [],
            ],
        ];
    }

    public function getPrice($options)
    {
        $duration = Time::getDT($options['duration']);

        if ($options['transfer_id']/* && $duration->isEmpty()*/) {
            $transferPrice = new TransfersGuides();
            $transferPrice->select()->where([
                'depend' => $options['transfer_id'],
                'lang_id' => $options['lang_id'],
            ]);

            if ($transferPrice->load()) {
                $transfer = new Transfer(['id' => $options['transfer_id']]);

                return [
                    'transfer_id' => $options['transfer_id'],
                    'name' => 'Гид:',
                    'duration' => $transfer->get('duration'),
                    'income' => $transferPrice->get('income'),
                    'outgo' => $transferPrice->get('outgo'),
                    'desc' => 'Маршрут "' . $transferPrice->plugin('transfer')->get('name') . '"',
                    'errors' => [],
                ];
            } else {
                return [
                    'transfer_id' => 0,
                    'name' => 'Гид:',
                    'income' => 0,
                    'outgo' => 0,
                    'desc' => '',
                    'duration' => '0000-00-00',
                    'errors' => [GuidesService::ERROR_GUIDES_TRANSFER_NOT_FOUND => 'Цена за трансфер не найдена']
                ];
            }
        }

        if ($options['income'] || $options['outgo']) {
            return [
                'transfer_id' => 0,
                'name' => 'Гид:',
                'duration' => $duration->format(),
                'income' => $options['income'],
                'outgo' => $options['outgo'],
                'desc' => 'Фиксированная стоимость',
                'errors' => [],
            ];
        }

        $timeFrom = $options['time'];

        $this->select()->where(['lang_id' => $options['lang_id']]);

        $guide = false;
        if($options['guide_id']) {
            $guide = (new Guide())->id($options['guide_id'])->load();
        }

        if($guide) {
            $pph = $guide->price;
        } else {
            $pph = $this->price;
        }

        //$resp['income'] = $pph * $duration;

        $timeFrom = Time::getDT($timeFrom);
        $timeTo   = (clone $timeFrom)->addition($duration);

        $dateRange = new \DatePeriod((clone $timeFrom)->round('up')->getDtObj(), new \DateInterval('PT1H'), (clone $timeTo)->round('down')->getDtObj());

        //Ночью в 1.5 раза дороже
        $priceDay = $pph;
        $priceNight = $priceDay * 1.5;

        $hours = ['day' => 0, 'night' => 0];

        //Hours
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
            if ($hour >= 22 || $hour <= 7) {
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

        $pphIncome = ($hours['day'] * $priceDay) + ($hours['night'] * $priceNight);
        $income = min(max($pphIncome, $this->get('min_price')), $this->get('max_price'));
        $income = round($income / 100) * 100;

        $outgo = $income;
        $guideName = 'Гид: ' . ($guide ? $guide->name : 'не указан');

        $desc = '';
        if($income == $this->min_price) {
            $desc .= 'мин. стомиость ' . $income . ' > ' . $pphIncome;
        }

        if($income == $this->max_price) {
            $desc .= 'макс. стомиость ' . $income . ' < ' . $pphIncome;
        }

        $desc .= $hours['day']   ? ' Д: ' . $hours['day']   . ' час. * ' . $priceDay .  ', ' : '';
        $desc .= $hours['night'] ? 'Н: ' . $hours['night'] . ' час. * ' . $priceNight .  ', ' : '';

        /*if($guide) {
            $guidePriceDay = $guide->price;
            $guidePriceNight = $guidePriceDay * 1.5;
            $outgo = max((($hours['day'] * $guidePriceDay) + ($hours['night'] * $guidePriceNight)), $this->min_price);
        }*/

        //$outgo = round($outgo / 100) * 100;

        return [
            'errors'    => [],
            'income'    => $income,
            'outgo'     => $outgo,
            'name'      => $guideName,
            'duration'  => $duration->format(),
            'desc'      => rtrim($desc, ', '),
        ];
    }
}