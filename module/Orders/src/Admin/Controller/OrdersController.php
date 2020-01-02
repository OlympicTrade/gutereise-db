<?php
namespace Orders\Admin\Controller;

use Pipe\Calendar\Calendar;
use Pipe\DateTime\Date;
use Pipe\Mail\Mail;
use Orders\Admin\Form\OrderDocumentsForm;
use Orders\Admin\Form\OrdersEditForm;
use Orders\Admin\Model\Order;
use Pipe\Mvc\Controller\Admin\TableController;
use Zend\Json;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class OrdersController extends TableController
{
    public function getListFields()
    {
        return [
            'date' => [
                'header'    => 'Дата',
                'filter'    => function($item, $view){
                    $dtFrom = $item->get('date_from');
                    return $dtFrom->day(false) . ' ' . \Pipe\String\Date::$monthsShort[$dtFrom->month(false)];
                },
                'width'     => '68',
            ],
            'name' => [
                'header'    => 'Название',
            ],
            'client' => [
                'header'    => 'Клиент',
                'filter'    => function($item, $view){
                    $client = $item->plugin('clients')->getFirst();

                    return $client ? $client->plugin('client')->get('name') : '';
                },
            ],
        ];
    }

    public function addDayAction()
    {
        $orderId = $this->params()->fromPost('orderId');
        $day = $this->getService()->addOrderDay($orderId);
        return new JsonModel(['html' => $this->viewHelper('OrderDayForm', $day)]);
    }

    public function delDayAction()
    {
        $dayId = $this->params()->fromPost('dayId');
        $this->getService()->delOrderDay($dayId);
        return new JsonModel(['status' => 1]);
    }

    public function checkAction()
    {
        $id   = $this->params()->fromPost('id');
        $data = $this->params()->fromPost();

        $order = (new Order(['id' => $id]));
        $order->load();

        $form = new OrdersEditForm();
        $form->setOptions(['model' => $order])
            ->init()
            ->setFilters()
            ->setData($data)
            ->isValid();

        $order->unserializeArray($form->getData());

        return new JsonModel($this->getOrderService()->calcOrder($order, ['calc_type' => $data['calc_type']]));
    }

    public function proposalAction()
    {
        $order = new Order([
            'id' => $this->params()->fromRoute('id')
        ]);

        if(!$order->load()) return $this->send404();

        $view = new ViewModel();
        $view->setTerminal('true');

        return $view->setVariables([
            'proposal' => $order->get('proposal'),
        ]);
    }

    public function calcProposalAction()
    {
        $data = $this->params()->fromPost();

        define('DD', 1);

        $order = new Order();

        $form = new OrdersEditForm();
        $form->setOptions(['model' => $order])
            ->init()
            ->setFilters()
            ->setData($data)
            ->isValid();

        $order->unserializeArray($form->getData());

        $html = $this->getOrderService()->getOrderProposalHtml($order);

        return new JsonModel(['html' => $html]);
    }

    public function emailsAction()
    {
        $data = $this->params()->fromPost();

        if($data['type'] == 'send') {
            $email = $data['email'];
            $proposal = $data['proposal'];

            if(MODE == 'dev' && !in_array($email, ['info@Pipe.ru', 'vks.ecommerce@gmail.com', 'vasiljeva.lubov.57@gmail.com'])) {
                return new JsonModel([]);
            }

            $mail = new Mail();
            $mail->setTemplate(MODULE_DIR . '/Orders/view/orders/admin/mail/order-proposal.phtml')
                ->setHeader('Gute Reise')
                ->addTo($email)
                ->setVariables([
                    'proposal'  => $proposal,
                ])
                ->send();

            return new JsonModel([]);
        }

        $order = new Order();
        $form = new OrdersEditForm();
        $form->setOptions(['model' => $order])
            ->init()
            ->setFilters()
            ->setData($data)
            ->isValid();

        $order->unserializeArray($form->getData());

        $emails = $this->getOrderService()->getOrderEmails($order);

        $view = new ViewModel();
        $view->setVariables([
            'emails' => $emails,
            'order'  => $order,
        ]);
        $view->setTerminal(true);

        return $view;
    }

    public function documentsAction()
    {

        $data = $this->params()->fromPost();
        $order = new Order(['id' => $data['oid']]);
        $order->load();

        $form = new OrderDocumentsForm();
        $form->setOptions(['model' => $order])->init();
        $form->setData([
            'order_id' => $order->id()
        ]);

        $view = new ViewModel();
        $view->setVariables([
            'form'  => $form,
        ]);

        $view->setTerminal(true);

        return $view;
    }

    public function googleCalendarAction()
    {
        $data = $this->params()->fromPost();

        $order = new Order(['id' => $data['oid']]);
        $gcalendar = $order->plugin('gcalendar');

        if($data['type'] == 'save') {
            $gcalendar->load();
            $gcalendar->unserializeArray($data);
            $gcalendar->save();

            return new JsonModel(['status' => 1]);
        }

        $oEmails = $this->getOrderService()->getOrderEmails($order);
        $gcEmails = $gcalendar->plugin('emails');

        $i = 0;
        foreach ($oEmails as $key => $row) {
            $i++;

            $email = [
                'name'   => $row['name'],
                'email'  => $row['email'],
                'active' => 0,
            ];

            $id = 'new-' . $i;

            foreach ($gcEmails as $gcEmail) {
                if($row['email'] == $gcEmail->get('email')) {
                    $email['active'] = $gcEmail->get('active');
                    $id = $gcEmail->id();
                    break;
                }
            }

            $emails[$id] = $email;
        }

        foreach ($gcEmails as $gcEmail) {
            $exists = false;
            foreach ($emails as $key => $row) {
                if($row['email'] == $gcEmail->get('email')) {
                    $emails[$key]['active'] = $gcEmail->get('active');
                    $exists = true;
                    break;
                }
            }

            if($exists) {
                continue;
            }

            $emails[$gcEmail->id()] = [
                'name'   => $gcEmail->get('email'),
                'email'  => $gcEmail->get('email'),
                'active' => $gcEmail->get('active'),
            ];
        }

        $view = new ViewModel();
        $view->setVariables([
            'order'     => $order,
            'gcalendar' => $gcalendar,
            'emails'    => $emails,
        ]);
        $view->setTerminal(true);

        return $view;
    }

    public function calendarAction()
    {
        return $this->forward()->dispatch('Orders\Controller\Orders', [
            'action' => 'index',
        ]);
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

        $calendar = new Calendar('orders', $dt);
        $calendar->save();

        return new JsonModel([
            'html'  => $this->getViewHelper('orderCalendarPage')($calendar, $this->getOrderService()),
            'date'  => $dt->format(),
            'year'  => $dt->year(),
            'month' => $dt->month(),
        ]);
    }

    public function indexAction()
    {
        $view = $this->initLayout();
        $view->setTemplate('orders/admin/orders/calendar');

        return $view->setVariables([
            'orderService'=> $this->getOrderService(),
        ]);
    }

    protected function getFiltersData($data, $type)
    {
        unset($data['page']);

        if($type != 'calendar') {
            if (!$data['date_from']) {
                if (isset($_COOKIE['orders_filter'])) {
                    $data['date_from'] = Json\Decoder::decode($_COOKIE['orders_filter'], Json\Json::TYPE_ARRAY);
                } else {
                    $data['date_from'] = date('01.m.Y');
                }
            }
            setcookie('orders_filter', Json\Encoder::encode($data['date_from']), time()+3600*24*30, '/');
        }

        return $data;
    }

    /*
    public function clientHtmlAction()
    {
        return new JsonModel(array(
            'html' => $this->viewHelper('clientAttache')
        ));
    }

    public function guideHtmlAction()
    {
        return new JsonModel(array(
            'html' => $this->viewHelper('GuideAttache')
        ));
    }

    public function driverHtmlAction()
    {
        return new JsonModel(array(
            'html' => $this->viewHelper('DriverAttache')
        ));
    }

    public function ticketHtmlAction()
    {
        return new JsonModel(array(
            'html' => $this->viewHelper('TicketAttache')
        ));
    }

    public function hotelHtmlAction()
    {
        return new JsonModel(array(
            'html' => $this->viewHelper('HotelAttache')
        ));
    }
*/
    /**
     * @return \Orders\Admin\Service\OrdersService
     */
    protected function getOrderService()
    {
        return $this->getServiceManager()->get('Orders\Admin\Service\OrdersService');
    }

    /**
     * @return \Orders\Admin\Service\CalcService
     */
    protected function getCalcService()
    {
        return $this->getServiceManager()->get('Orders\Admin\Service\CalcService');
    }
}