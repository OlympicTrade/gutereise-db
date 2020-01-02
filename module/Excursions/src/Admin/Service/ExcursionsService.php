<?php

namespace Excursions\Admin\Service;

use Pipe\Mvc\Service\Admin\TableService;
use Excursions\Admin\Model\ExcursionDay;

class ExcursionsService extends TableService
{
    const ERROR_NOT_FOUND    = 301;
    const ERROR_MIN_MAX_TIME = 302;

    public function delExcursionDay($dayId)
    {
        $day = new ExcursionDay();
        $day->id($dayId);

        if($day->load()) {
            $day->remove();
        }
    }

    public function addExcursionDay($orderId)
    {
        $day = new ExcursionDay();
        $day->setVariables([
            'depend'    => $orderId,
            'time_from' => '12:00:00',
        ]);

        $daysCount = ExcursionDay::getEntityCollection();
        $daysCount->select()
            ->where(['depend' => $orderId]);

        $day->set('sort', $daysCount->count() + 1, true);
        $day->save();

        return $day;
    }

    public function getExtraList($dayData, $commonData)
    {
        $extraRes = [
            'autocalc'  => (int) $dayData['extra']['autocalc'],
            'list'      => [],
            'errors'    => [],
            'income'    => 0,
            'outgo'     => 0,
        ];

        if($commonData['calc_type'] == 'proposal' || !$extraRes['autocalc']) {
            if($dayData['extra']) {
                $extraRes = $dayData['extra'] + $extraRes;

                foreach ($extraRes['list'] as $key => $row) {
                    $extraRes['income'] += $row['income'];
                    $extraRes['outgo'] += $row['outgo'];
                    $extraRes['list'][$key]['errors'] = [];
                }
            }
            return $extraRes;
        }

        $exDay = new ExcursionDay(['id' => $dayData['day_id']]);
        $extraPl = $exDay->plugin('extra', $commonData);

        foreach ($extraPl as $row) {
            $extraPrice = $row->getPrice($commonData);

            $extraRes['list'][] = [
                'errors'        => [],
                'name'          => $row->get('name'),
                'proposal_name' => $row->get('proposal_name'),
                'income'        => $extraPrice['income'],
                'outgo'         => $extraPrice['outgo'],
            ];

            $extraRes['income'] += $extraPrice['income'];
            $extraRes['outgo']  += $extraPrice['outgo'];
        }

        return $extraRes;
    }
}