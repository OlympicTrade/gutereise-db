<?php
namespace Orders\Admin\Controller;

use Application\Admin\Model\Nationality;
use Orders\Admin\Service\CalcService;
use Orders\Admin\Service\ProposalService;
use Pipe\DateTime\Date;
use Pipe\Mvc\Controller\Admin\AbstractController;
use Pipe\String\Translit;
use Excursions\Admin\Model\Excursion;
use Excursions\Admin\Model\ExcursionDay;
use Excursions\Admin\Model\ExcursionMuseums;
use Hotels\Admin\Model\Hotel;
use Museums\Admin\Model\Museum;
use Orders\Admin\Form\CalcDayForm;
use Orders\Admin\Form\CalcForm;
use Orders\Admin\Model\Order;
use Orders\Admin\Model\OrderDay;
use Transports\Admin\Service\TransportsService;
use Zend\Db\Sql\Where;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class CalcController extends AbstractController
{
    public function addDayAction()
    {
        $data = $this->params()->fromPost();

        $form = (new CalcDayForm())
            ->setPrefix('days[' . $this->params()->fromPost('tabNbr') . ']')
            ->setOptions()
            ->init();

        if($data['date']) {
            $dt = Date::getDT($data['date']);
        } else {
            $dt = (new \DateTime())->modify('+1 day');
            if (!empty($data['days'])) {
                foreach ($data['days'] as $dayData) {
                    $dDt = \DateTime::createFromFormat('d.m.Y', $dayData['date'])->modify('+1 day');

                    if ($dDt > $dt) {
                        $dt = $dDt;
                    }
                }
            }
        }

        $form->setData([
            $form->getElName('date') => $dt->format('d.m.Y'),
            $form->getElName('time') => '10:00:00'
        ]);

        return new JsonModel(['html' => $this->getViewHelper('calcDayForm')($form)]);
    }

    public function addHotelAction()
    {
        $hotelId = $this->params()->fromPost('hid');

        $hotel = new Hotel(['id' => $hotelId]);

        if(!$hotel->load()) return $this->send404();

        $form = new \Orders\Admin\Form\CalcHotelForm();
        $form->setOptions(['hotel' => $hotel]);
        $form->init();

        return new JsonModel(['html' => $this->getViewHelper('CalcHotelRooms')($form, $hotel)]);
    }

    public function findTransportAction()
    {
        $count = (int) $this->params()->fromPost('adults') + (int) $this->params()->fromPost('children');
        $type = $this->params()->fromPost('type');

        $transport = $this->getTransportsService()->getTransportByCapacity($count, $type);

        if($transport) {
            $resp = [
                'id'        => $transport->id(),
                'name'      => $transport->get('name'),
                'price'     => $transport->get('price'),
                'capacity'  => $transport->get('capacity'),
                'desc'      => $transport->get('capacity') . ' чел.',
            ];
        } else {
            $resp = [
                'id'        => 0,
                'name'      => 'Транспорт не найден',
                'price'     => 0,
                'capacity'  => 0,
                'desc'      => 'Нет данных',
            ];
        }

        return new JsonModel($resp);
    }

    public function orderAction()
    {
        $data = $this->params()->fromPost();
        $order = $this->getCalcService()->addOrder($data);

        return new JsonModel(['id' => $order->id()]);
    }

    public function calcAction()
    {
        if($this->getRequest()->isPost()) {
            $data = $this->getCalcService()->calc($this->params()->fromPost());
            return new JsonModel($data);
        }

        $view = new ViewModel();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $view->setTerminal(true);
        } else {
            $this->initLayout();
        }


        $order = false;
        $orderDay = false;
        if($orderId = $this->params()->fromQuery('oid')) {
            $order = new Order(['id' => $orderId]);
            $orderDay = new OrderDay(['id' => $this->params()->fromQuery('did')]);
        }

        $form = new CalcForm();

        if($order) {
            $form->setData([
                'order_id' => $order->id(),
                'day_id'   => $this->params()->fromQuery('did'),
                'lang_id'  => $order->get('lang_id'),
                'agency'   => $order->get('agency'),
                'adults'   => $order->get('adults'),
                'children' => $order->get('children'),
            ]);
        } else {
            if(MODE == 'dev') {
                $form->setData([
                    'adults'   => 8,
                    'children' => 0,
                ]);
            }
        }

        $view->setVariables([
            'form'      => $form,
            'order'     => $order,
            'orderDay'  => $orderDay,
        ]);

        return $view;
    }

    public function museumsAutocompleteAction()
    {
        $query = $this->params()->fromPost('query');
        $resp = [];

        $museums = Museum::getEntityCollection();
        $museums->select()
            ->limit(15);

        $where = new Where();
        foreach(Translit::searchVariants($query) as $queryVar) {
            $where->or->like('name', '%' . $queryVar . '%');
        }

        $museums->select()
            ->where
                ->addPredicate($where);

        foreach ($museums as $museum) {
            $name = str_replace(['"'], [''], $museum->get('name'));

            $resp[] = [
                'id'    => $museum->id(),
                'label' => $name,
            ];
        }

        return new JsonModel($resp);
    }

    public function hotelsAutocompleteAction()
    {
        $query = $this->params()->fromPost('query');

        $hotels = Hotel::getEntityCollection();
        $hotels->select()
            ->limit(15);

        $where = new Where();
        foreach(Translit::searchVariants($query) as $queryVar) {
            $where->or->like('name', '%' . $queryVar . '%');
        }

        $hotels->select()
            ->where
                ->addPredicate($where);

        $resp = [];

        foreach($hotels as $hotel) {
            $hotelData = [
                'id'    => $hotel->id(),
                'label' => $hotel->get('name'),
                'rooms' => []
            ];

            foreach($hotel->plugin('rooms') as $room) {
                $hotelData['rooms'][] = [
                    'id'    => $room->id(),
                    'name'  => $room->get('name'),
                ];
            }

            $resp[] = $hotelData;
        }

        return new JsonModel($resp);
    }

    public function toursAutocompleteAction()
    {
        $query  = $this->params()->fromPost('query');
        $langId = $this->params()->fromPost('langId');
        $date   = $this->params()->fromPost('date');
        $resp   = [];

        $tours = Excursion::getEntityCollection();
        $tours->select()
            ->order('hits DESC')
            ->limit(15);

        $where = new Where();
        foreach(Translit::searchVariants($query) as $queryVar) {
            $where->or->like('name', '%' . $queryVar . '%');
        }

        $tours->select()
            ->where
                //->greaterThan('days',0)
                ->addPredicate($where);

        $firstDay = Date::getDT($date['first']);
        $lastDay = Date::getDT($date['last']);

        //$dt = Date::getDT($date);
        foreach ($tours as $tour) {
            $daysCount = $tour->days()->count();

            $name = str_replace(['"'], [''], $tour->get('name'));

            $tourData = [
                'id'    => $tour->id(),
                'label' => trim($name),
                'type'  => ($daysCount > 1 ? 'tour' : 'excursion'),
            ];

            if($daysCount > 1) {
                $date = clone $firstDay;
            } else {
                $date = clone $lastDay;
            }

            foreach ($tour->plugin('days') as $day) {
                //$attrs = $day->plugin('attrs');
                $options = $day->options;

                $dayData = [
                    'date'       => $date->format('d.m.Y'),
                    'margin'     => $tour->get('margin'),
                    'day_id'     => $day->id(),
                    'min_time'   => $day->get('min_time'),
                    'max_time'   => $day->get('max_time'),
                    'transfer_id'=> $day->get('transfer_id'),
                    'transports' => [],
                    'museums'    => [],
                    'transfer_time'      => $day->get('transfer_time'),
                    'car_delivery_time'  => $day->get('car_delivery_time'),
                    'duration'   => $day->getDuration(),
                    'proposal'   => [
                        'place_start' => $options['proposal']['place_start'],
                        'place_end'   => $options['proposal']['place_end'],
                        'price'       => $options['proposal']['price'],
                        'autocalc'    => [
                            'guides'     => $options['proposal']['price']['guides'],
                            'museums'    => $options['proposal']['price']['museums'],
                            'transports' => $options['proposal']['price']['transport'],
                        ],
                    ],
                ];

                /** @var ExcursionMuseums $museums */
                $museums = $day->plugin('museums');
                $museums->select()->where
                    ->in('foreigners', Nationality::langToNationality($langId));

                foreach ($museums as $row) {
                    $dayData['museums'][] = [
                        'id' => $row->get('museum_id'),
                        'duration' => $row->get('duration'),
                    ];
                }

                foreach ($day->plugin('transport') as $row) {
                    $dayData['transports'][] = [
                        'id'            => $row->get('transport_id'),
                        'transfer_id'   => $row->get('transfer_id'),
                        'duration'      => $row->get('duration'),
                        'type'          => $row->get('type'),
                    ];
                }

                $tourData['days'][] = $dayData;
                $date->modify('+1 day');
            }

            $resp[] = $tourData;
        }

        return new JsonModel($resp);
    }

    public function saveTextAction()
    {
        $text = $this->params()->fromPost('text');
        $session = new Container();

        if($text) {
            $session->print = $text;
        }

        die();
    }

    public function proposalDataAction()
    {
        $commonData = $this->params()->fromPost('commonData');
        $dayData = $this->params()->fromPost('dayData');

        $exDay = (new ExcursionDay())->id($dayData['day_id'])->load();

        if(!$exDay) {
            return $this->send404();
        }

        $timetablePl = $exDay->plugin('timetable', [
            'foreigners' => Nationality::langToNationality($commonData['lang_id']),
            'tourists'   => ($commonData['adults'] + $commonData['children'])
        ]);

        $timetable = [];
        foreach ($timetablePl as $row) {
            $timetable[] = [
                'duration' => $row->get('duration'),
                'name'     => $row->get('name'),
            ];
        };

        $extratablePl = $exDay->plugin('extra', [
            'foreigners' => Nationality::langToNationality($commonData['lang_id']),
            'tourists'   => ($commonData['adults'] + $commonData['children'])
        ]);

        $extralist = [];
        foreach ($extratablePl as $row) {
            $extraPrice = $row->getPrice(['tourists' => $commonData['adults'] + $commonData['children']]);
            $extralist[] = [
                'name'          => $row->get('name'),
                'proposal_name' => $row->get('proposal_name'),
                'income'        => $extraPrice['income'],
                'outgo'         => $extraPrice['outgo'],
            ];
        };

        return new JsonModel([
            'pricetable' => $this->getProposalService()->getPriceTable($dayData, $commonData),
            'timetable'  => $timetable,
            'extralist'  => $extralist,
        ]);
    }

    public function proposalAction()
    {
        $view = new ViewModel();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $view->setTerminal(true);
        } else {
            $this->initLayout();
        }

        $text = $this->getProposalService()->getProposalHtml(
            $this->getProposalService()->getProposalData(
                $this->getCalcService()->calc($this->params()->fromPost())
            )
        );
        $view->setVariables(['text' => $text]);

        return $view;
    }

    public function wordAction()
    {
        $session = new Container();
        $this->getProposalService()->word($session->print);
        die();
    }

    public function printAction()
    {
        $session = new Container();

        $view = new ViewModel();
        $view->setVariables(['text' => $session->print]);
        $view->setTerminal(true);
        return $view;
    }

    public function emailAction()
    {
        $session = new Container();

        $file = $this->getCalcService()->word($session->print, true);

        $mail = new \Pipe\Mail\Mail();
        $mail->setTemplate(MODULE_DIR . '/Orders/view/orders/calc/mail.phtml')
            ->setHeader('Коммерческое предложение')
            ->addTo($this->params()->fromPost('email'))
            ->setAttachment($file, 'Индивидуальная экскурсия.docx')
            ->setVariables(['text' => $session->print]);

        try {
            $mail->send();
        } catch (\Exception $e) {
            return new JsonModel(['status' => 'fail']);
        }

        return new JsonModel(['status' => 'success']);
    }

    public function generate($url = '/calc/', $meta = false)
    {
        return parent::generate($url, $meta);
    }

    /**
     * @return CalcService
     */
    protected function getCalcService()
    {
        return $this->getServiceManager()->get(CalcService::class);
    }

    /**
     * @return ProposalService
     */
    protected function getProposalService()
    {
        return $this->getServiceManager()->get(ProposalService::class);
    }

    /**
     * @return TransportsService
     */
    protected function getTransportsService()
    {
        return $this->getServiceManager()->get(TransportsService::class);
    }
}