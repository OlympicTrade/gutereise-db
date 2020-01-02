<?php
namespace Sync\Admin\Controller;

use Pipe\Mvc\Controller\AbstractController;
use Sync\Admin\Service\PriceService;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;

class SyncController extends AbstractController
{
    public function getSettingsDataAction()
    {
        $data = $this->getPriceService()->getSettingsData();

        return new JsonModel($data);
    }

    public function getExcursionDataAction()
    {
        $excursionId = $this->params()->fromQuery('id');

        $data = $this->getPriceService()->getExcursionData($excursionId);
        //dd($data);

        return new JsonModel($data);
    }

    public function getTransportDataAction()
    {
        $transportId = $this->params()->fromQuery('id');

        $data = $this->getPriceService()->getTransportData($transportId);

        return new JsonModel($data);
    }

    public function getPriceAction()
    {
        $data = $this->params()->fromQuery();
		
        $price = $this->getPriceService()->calcPrice($data);
        //dd($price);

        return new JsonModel($price);
    }

    public function addOrderAction()
    {
        $data = $this->params()->fromQuery();

        $params = [
            'currency'      => $data['currency'],
            'client_name'   => $data['client_name'],
            'client_phone'  => $data['client_phone'],
            'client_email'  => $data['client_email'],
            'excursion_id'  => $data['excursion_id'],
            'adults'        => $data['adults'],
            'children'      => $data['children'],
            'lang_id'       => $data['lang_id'],
            'date'          => $data['date'],
            'time'          => $data['time'],
        ];

        $order = $this->getPriceService()->addOrder($params);

        return new JsonModel(['orderId' => $order->id()]);
    }

    public function indexAction()
    {
        $type = $this->params()->fromPost('sync-type');
        $data = $this->params()->fromPost('data');
        $data = (array) Json::decode($data);

        if(!$this->getSyncService()->checkKey()) {
            die('fail');
        }

        switch($type) {
            case 'export-client':
                $id = $this->getSyncService()->addClient($data);
                break;
            case 'export-order':
                $id = $this->getSyncService()->addOrder($data);
                break;
            case 'export-hotel':
                $id = $this->getSyncService()->addHotel($data);
                break;
            default:
                die('fail');
        }

        die((string) $id);
    }

    /**
     * @return \Sync\Admin\Service\PriceService
     */
    public function getPriceService()
    {
        return $this->getServiceManager()->get('Sync\Admin\Service\PriceService');
    }

    /**
     * @return \Sync\Admin\Service\SyncService
     */
    public function getSyncService()
    {
        return $this->getServiceManager()->get('Sync\Admin\Service\SyncService');
    }
}