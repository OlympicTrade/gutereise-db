<?php

namespace Orders\Admin\Service;

use Pipe\DateTime\Date;
use Pipe\Mvc\Service\AbstractService;
use Pipe\Mvc\Service\Admin\TableService;
use Clients\Admin\Model\Client;
use Drivers\Admin\Model\Driver;
use Guides\Admin\Model\Guide;
use Museums\Admin\Model\Museum;
use Orders\Admin\Model\OrderClients;
use Orders\Admin\Model\OrderDayGuides;
use Orders\Admin\Model\OrderDayMuseums;
use Orders\Admin\Model\OrderDayTransport;
use Zend\Db\Sql\Expression;

class BalanceService extends TableService
{
    public function getBalance(Date $dtFrom, Date $dtTo)
    {
        $sql = $this->getSql();

        $select = $sql->select();
        $select->from(['o' => 'orders'])
            ->columns([
                'income'    => new Expression('SUM(o.income)'),
                'outgo'     => new Expression('SUM(o.outgo)'),
                'profit'    => new Expression('SUM(o.income - o.outgo)'),
                'tourists'  => new Expression('SUM(o.adults + o.children)'),
            ])
            //->group('o.id')
            ->where
                ->greaterThanOrEqualTo('date_from', $dtFrom->format('Y-m-d'))
                ->lessThanOrEqualTo('date_from', $dtTo->format('Y-m-d'));

        $result = $this->execute($select)->current();

        return $result;
    }

    public function getTransportBalance()
    {
        $driver = new Driver();
        $driver->addProperty('balance');
        $drivers = $driver->getCollection();

        $drivers->select()
            ->columns(['id', 'name', 'phone'])
            ->join(['odt' => 'orders_days_transport'], 't.id = odt.driver_id', ['balance' => new Expression('SUM(odt.outgo)')])
            ->group('t.id')
            ->where(['odt.paid' => 0]);

        return $drivers;
    }

    public function getGuidesBalance()
    {
        $guide = new Guide();
        $guide->addProperty('balance');
        $guides = $guide->getCollection();

        $guides->select()
            ->columns(['id', 'name'])
            ->join(['odg' => 'orders_days_guides'], 't.id = odg.guide_id', ['balance' => new Expression('SUM(odg.outgo)')])
            ->group('t.id')
            ->where(['odg.paid' => 0]);

        return $guides;
    }

    public function getMuseumsBalance()
    {
        $museum = new Museum();
        $museum->addProperty('balance');
        $museums = $museum->getCollection();

        $museums->select()
            ->columns(['id', 'name', 'phone'])
            ->join(['odm' => 'orders_days_museums'], 't.id = odm.museum_id',
                ['balance' => new Expression('SUM(odm.guides + odm.tickets_adults + odm.tickets_children + odm.extra)')])
            ->group('t.id')
            ->where(['odm.paid' => 0]);

        return $museums;
    }

    public function getClientsBalance()
    {
        $client = new Client();
        $client->addProperty('balance');
        $clients = $client->getCollection();

        $clients->select()
            ->columns(['id', 'name'])
            ->join(['oc' => 'orders_clients'], 't.id = oc.client_id', [])
            ->join(['o' => 'orders'], 'o.id = oc.depend', ['balance' => new Expression('SUM(o.income)')])
            ->group('t.id')
            ->where(['oc.paid' => 0]);

        return $clients;
    }

    public function setStatusPaid($type, $ids)
    {
        switch ($type) {
            case 'transport':
                $coll = OrderDayTransport::getEntityCollection();
                break;
            case 'guides':
                $coll = OrderDayGuides::getEntityCollection();
                break;
            case 'museums':
                $coll = OrderDayMuseums::getEntityCollection();
                break;
            case 'clients':
                $coll = OrderClients::getEntityCollection();
                break;
            default:
                return false;
        }

        $coll->select()->where(['id' => $ids]);
        $coll->set('paid', 1)->save();

        return true;
    }
}