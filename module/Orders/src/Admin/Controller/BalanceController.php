<?php
namespace Orders\Admin\Controller;

use Pipe\Calendar\Calendar;
use Pipe\DateTime\Date;
use Pipe\Mvc\Controller\Admin\AbstractController;
use Zend\View\Model\JsonModel;

class BalanceController extends AbstractController
{
    public function indexAction()
    {
        $this->initLayout();
    }

    public function getCalendarPageAction()
    {
        $data = $this->params()->fromPost();

        $dt = Date::getDT($data['date']);

        switch ($data['shift']) {
            case 'prev':
                $dt->modify('-1 month');
                break;
            case 'next':
                $dt->modify('+1 month');
                break;
            default:
        }

        $calendar = new Calendar('balance', $dt);
        $calendar->save();

        $yDate = Date::getDT($dt->format('Y-01-01'));
        $mDate = Date::getDT($dt->format('Y-m-01'));
        $aDate = Date::getDT($dt->format('1990-01-01'));

        $yBalance = $this->getBalanceService()->getBalance($yDate, (clone $yDate)->modify('+1 year'));
        $mBalance = $this->getBalanceService()->getBalance($mDate, (clone $mDate)->modify('+1 month'));
        $aBalance = $this->getBalanceService()->getBalance($aDate, (clone $aDate)->modify('+100 years'));

        return new JsonModel([
            'html'    => $this->getViewHelper('balanceCalendarPage')->__invoke($calendar, [
                \Pipe\String\Date::$months[$dt->month(false)] => $mBalance,
                $dt->year() . ' год' => $yBalance,
                'Все время' => $aBalance,
            ]),
            'date'    => $dt->format(),
            'year'    => $dt->year(),
            'month'   => $dt->month(),
        ]);
    }

    public function oldAction()
    {
        $this->initLayout();
        $balanceService = $this->getBalanceService();

        $from = new \DateTime(date('Y-m-01'));
        $to = (clone $from)->modify('+1 month');

        return [
            'balance'   => [
                '-2m' => $balanceService->getBalance((clone $from)->modify('-2 month'), (clone $to)->modify('-1 month')),
                '-1m' => $balanceService->getBalance((clone $from)->modify('-1 month'), (clone $to)->modify('-2 month')),
                '1m' => $balanceService->getBalance($from, $to),
                '+1m' => $balanceService->getBalance((clone $from)->modify('-1 month'), (clone $to)->modify('-2 month')),
                'y'  => $balanceService->getBalance(new \DateTime(date('Y-01-01')), new \DateTime(date('Y-12-31'))),
            ],
        ];
    }
/*
    public function setPaidAction()
    {
        $ids  = $this->params()->fromPost('ids');
        $type = $this->params()->fromPost('type');

        return new JsonModel([
            'status' => (int) $this->getBalanceService()->setStatusPaid($type, $ids)
        ]);
    }*/

    /**
     * @return \Orders\Admin\Service\BalanceService
     */
    protected function getBalanceService()
    {
        return $this->getServiceManager()->get('Orders\Admin\Service\BalanceService');
    }
}