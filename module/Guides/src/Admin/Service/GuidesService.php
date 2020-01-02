<?php

namespace Guides\Admin\Service;

use Pipe\DateTime\Date;
use Pipe\DateTime\Time;
use Pipe\Mvc\Service\Admin\TableService;
use Guides\Admin\Model\Guide;
use Guides\Admin\Model\GuidePrice;
use Zend\Db\Sql\Expression as SExpression;
use Zend\Db\Sql\Predicate\Expression as PExpression;

class GuidesService extends TableService
{
    const ERROR_GUIDES_TRANSFER_NOT_FOUND = 601;

    public function calcPriceManual($data)
    {
        $list = $data['list'];

        $result = [
            'errors' => [],
            'count'  => count($list),
            'income' => 0,
            'outgo'  => 0,
            'desc'   => '',
            'list'   => []
        ];

        if(!$result['count']) {
            return $result;
        }

        $gPrice = new GuidePrice();

        foreach ($list as $guide) {
            if(!$guide['duration'] && !$data['transfer_id']) {
                continue;
            }

            $price = $gPrice->getPrice([
                'lang_id'       => $data['lang_id'],
                'duration'      => $guide['duration'],
                'guide_id'      => $guide['guide_id'],
                'transfer_id'   => $data['transfer_id'],
                'time'          => $data['time'],
                'income'        => $guide['income'],
                'outgo'         => $guide['outgo'],
            ]);

            $result['list'][] = [
                'errors'   => $price['errors'],
                'desc'     => $price['desc'],
                'name'     => $price['name'],
                'duration' => $price['duration'],
                'income'   => $price['income'],
                'outgo'    => $price['outgo'],
                'guide_id' => $guide['guide_id'] ?? 0,
                'transfer_id' => $price['transfer_id'] ?? 0,
            ];

            $result['income'] += $price['income'];
            $result['outgo']  += $price['outgo'];
        }

        return $result;
    }

    public function calcPriceAuto($data)
    {
        $guides = $data['guidesCount'];

        $result = [
            'count'  => $guides,
            'income' => 0,
            'outgo'  => 0,
            'list'   => []
        ];

        $durationDt = Time::getDT($data['duration']);

        if(!$durationDt || $data['duration'] == '00:00:00' || !$data['duration']) {
            return $result;
        }

        $gPrice = new GuidePrice();

        $price = $gPrice->getPrice([
            'lang_id'       => $data['lang_id'],
            'transfer_id'   => $data['transfer_id'],
            'duration'      => $data['duration'],
            'time'          => $data['time']
        ]);

        $result['income'] = $guides * $price['income'];
        $result['outgo'] = $result['income'];

        for($i = 0; $i < $guides; $i++) {
            $result['list'][] = [
                'duration'      => $price['duration'],
                'transfer_id'   => $price['transfer_id'],
                'name'          => $price['name'],
                'income'        => $price['income'],
                'outgo'         => $price['outgo'],
                'desc'          => $price['desc'],
                'errors'        => $price['errors'],
            ];
        }

        return $result;
    }

    public function getGuides($props)
    {
        $guides = Guide::getEntityCollection();
        $guides->select()
            ->columns(['id', 'options'])
            ->group('t.id');

        if($props['lang_id']) {
            $guides->select()
                ->join(['gl' => 'guides_languages'], 'gl.depend = t.id', [])
                ->where(['gl.lang_id' => $props['lang_id']]);
        }

        if($props['museums_ids']) {
            $guides->select();

            $select = $this->getSql()->select('guides_museums')
                ->columns(['count' => new SExpression('COUNT(*)')])
                ->where([
                    'museum_id' => $props['museums_ids'],
                ]);
            $select->where->addPredicate(new PExpression('depend = t.id'));

            $guides->select()->where
                ->addPredicate(
                    new PExpression(count($props['museums_ids']) . ' = (' . $this->getSql()->buildSqlString($select) . ')')
                );
        }

        $dt = Date::getDT($props['date']);

        $result = [];
        foreach ($guides as $guide) {
            $result[] = [
                'id'     => (string) $guide->id(),
                'status' => (int) !$guide->isBusy($dt),
            ];
        }

        return $result;
    }
}