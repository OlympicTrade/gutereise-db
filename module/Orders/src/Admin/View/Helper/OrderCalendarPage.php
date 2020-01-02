<?php
namespace Orders\Admin\View\Helper;

use Pipe\Calendar\View\Helper\CalendarPage;
use Pipe\DateTime\Date;

class OrderCalendarPage extends CalendarPage
{
    public function __invoke($calendar, $orderService)
    {
        return $this->getView()->calendar($calendar, [
            'calendar' => [
                'body' => function(Date $dayDt) use ($orderService) {
                    $orders = $orderService->getOrders(['date_from' => $dayDt->format(), 'date_to' => $dayDt->format()]);
                    $orders->select()->order('days_count DESC');

                    return $this->calendarRow($dayDt, $orders, [
                        'attrs' => function($item, $attrs) {
                            $bg = $item->get('options')->color ?? '#b5a410';

                            return [
                                'style'  => $attrs['style'] . 'background: ' . $bg . ';',
                                'href'   => $item->getUrl(),
                                'data-status' => $item->get('status') . '-' . $item->get('errors'),
                            ] + $attrs;
                        },
                    ]);
                }
            ]
        ])->calendar();
    }
}