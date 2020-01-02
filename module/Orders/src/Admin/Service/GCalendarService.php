<?php

namespace Orders\Admin\Service;

use Orders\Admin\Model\Order;
use Pipe\Google\Client;
use Pipe\Google\Color;
use Pipe\Mvc\Service\AbstractService;

class GCalendarService extends AbstractService
{
    public function updateGoogleCalendar(Order $order)
    {
        if(!ONLINE) return;

        $gcalendar = $order->plugin('gcalendar');
        if(!$gcalendar->get('active')) {
            return;
        }

        if(MODE == 'dev') {
            $calendarId = 'vks.ecommerce@gmail.com';
        } elseif(MODE == 'test') {
            $calendarId = 'vasiljeva.lubov.57@gmail.com';
        } else {
            $calendarId = 'entdeckungsservice@gmail.com';
        }

        $service = new \Google_Service_Calendar(Client::getInstance()->getClient());

        $update = false;


        if($gcalendar->get('calendar_id')) {
            try {
                $event = $service->events->get($calendarId, $gcalendar->get('calendar_id'));
                $update = true;
            } catch (\Exception $e) {
                $event = new \Google_Service_Calendar_Event();
                $gcalendar->set('calendar_id', '');
                $update = false;
            }
        } else {
            $event = new \Google_Service_Calendar_Event();
        }

        /*if($event->status != 'confirmed') {
            $event = new \Google_Service_Calendar_Event();
            $gcalendar->set('calendar_id', '');
            $update = false;
        }*/

        $event->setSummary('summary');
        $event->setLocation('ул. Пушкина, д. Колотушкина');
        $event->setDescription('Тестовая запись');
        $event->setStart(new \Google_Service_Calendar_EventDateTime([
            'dateTime' => (new \DateTime())->format('Y-m-d\TH:i:sP'),
            'timeZone' => 'Europe/Moscow',
        ]));
        $event->setEnd(new \Google_Service_Calendar_EventDateTime([
            'dateTime' => (new \DateTime())->modify('+1 day')->format('Y-m-d\TH:i:sP'),
            'timeZone' => 'Europe/Moscow',
        ]));

        $colorId = 11;
        $event->setColorId($colorId);

        switch ($order->get('status')) {
            case Order::STATUS_NEW:
                $colorId = Color::$colors['red'];
                break;
            case Order::STATUS_PROCESS:
                if($order->get('errors')) {
                    $colorId = Color::$colors['yellow'];
                } else {
                    $colorId = Color::$colors['green'];
                }
                break;
            case Order::STATUS_CANCELED:
                $colorId = Color::$colors['gray'];
                break;
            default:
                break;
        }

        $event->setColorId($colorId);

        if(MODE == 'public') {
            $emails[] = [
                'email' => 'vks.ecommerce@gmail.com',
                'responseStatus' => 'needsAction',
            ];
        }

        $emails = [];
        foreach ($gcalendar->plugin('emails') as $email) {
            if(!$email->get('active')) continue;

            $emails[] = [
                'email'          => $email->get('email'),
                'responseStatus' => 'needsAction',
            ];
        }

        if($emails) {
            $event->setAttendees($emails);
        }

        $event->setSource(new \Google_Service_Calendar_EventSource([
            'title'	=> 'База данных',
            'url'   => 'http://' . $_SERVER['HTTP_HOST'] . $order->getUrl()
        ]));

        $event->setSummary($order->get('name'));

        $firstDay = false;
        $lastDay = false;

        foreach ($order->plugin('days') as $day) {
            $firstDay = $firstDay ?? $day;
            $lastDay = $day;
        }

        $firstDay = $order->plugin('days')->rewind()->current();
        $event->setLocation($firstDay->get('options')->proposal->place_start);

        $event->setDescription(str_replace(["\n", "\r"], '', $order->get('proposal')));

        $dtStart = \DateTime::createFromFormat('Y-m-d H:i:s', $order->get('date_from')->format() . ' ' . $firstDay->get('time')->format());
        $dtEnd   = \DateTime::createFromFormat('Y-m-d H:i:s', $order->get('date_to')->format() . ' ' . $lastDay->get('time')->format());

        $event->setStart(new \Google_Service_Calendar_EventDateTime([
            'dateTime' => $dtStart->format('Y-m-d\TH:i:sP'),
            'timeZone' => 'Europe/Moscow',
        ]));
        $event->setEnd(new \Google_Service_Calendar_EventDateTime([
            'dateTime' => $dtEnd->format('Y-m-d\TH:i:sP'),
            'timeZone' => 'Europe/Moscow',
        ]));

        $event->setReminders(new \Google_Service_Calendar_EventReminders([
            'useDefault' => false,
            'overrides' => [
                ['method' => 'email', 'minutes' => 24 * 60],
                ['method' => 'popup', 'minutes' => 24 * 60],
            ]
        ]));

        if($update) {
            $service->events->update($calendarId, $event->id(), $event);
        } else {
            $event = $service->events->insert($calendarId, $event);
        }

        if($event->status == 'confirmed') {
            $gcalendar->set('calendar_id', $event->id());
        }

        $gcalendar->save();
    }

    /**
     * @return \Orders\Admin\Service\OrdersService
     */
    protected function getOrdersService()
    {
        return $this->getServiceManager()->get('Orders\Admin\Service\OrdersService');
    }
}