<?php
namespace Sync\Admin\Service;

use Clients\Admin\Model\Client;
use Hotels\Admin\Model\Hotel;
use Orders\Admin\Model\Order;
use Zend\Db\Sql\Sql;

class SyncService
{
    const SYNC_KEY = '2mn34b5jgcva678';

    /**
     * @var Sql
     */
    protected $sql;

    public function addClient($data)
    {
        $client = new Client();
        $client->select()->where
            ->equalTo('phone', $data['phone'])
            ->or
            ->equalTo('email', $data['email']);

        if($client->load()) {
            return $client->id();
        }

        $client->setVariables(array(
            'name'          => $data['name'],
            'email'         => $data['email'],
            'phone'         => $data['phone'],
            'type'          => 2,
        ))->save();

        return $client->id();
    }

    public function addHotel($data)
    {
        $hotel = new Hotel();

        $hotel->select()->where
            ->equalTo('name', $data['name'])
            ->or
            ->equalTo('id', $data['id']);

        if($hotel->load()) {
            return $hotel->id();
        }

        $hotel->setVariables(array(
            'name'          => $data['name'],
        ))->save();

        return $hotel->id();
    }

    public function addOrder($data)
    {
        $order = new Order();
        $order->unserializeArray($data);

        $order->save();

        return $order->id();
    }

    public function checkKey()
    {
        return $_POST['key'] == self::SYNC_KEY;
    }
}