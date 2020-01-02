<?php
namespace Guides\View\Helper;

use Pipe\Calendar\View\Helper\CalendarPage;
use Pipe\DateTime\Date;
use \Guides\Admin\Model\GuideCalendar;

class GuidesCalendarPage extends CalendarPage
{
    public function __invoke($calendar, $guide, $orderService)
    {
        $defStatus = $guide->get('options')->calendar->status ?? 'free';
        $revStatus = $defStatus == 'free' ? 'busy' : 'free';

        $busyDays = GuideCalendar::getEntityCollection();
        $busyDays->select()->where
            ->greaterThanOrEqualTo('date', $calendar->getDt()->format('Y-m-01'))
            ->lessThanOrEqualTo('date', $calendar->getDt()->format('Y-m-31'))
            ->equalTo('busy', $revStatus)
            ->equalTo('depend', $guide->id());

        $bDays = [];
        foreach ($busyDays as $row) {
            $bDays[] = $row->get('date');
        }

        return $this->getView()->calendar($calendar, [
            'calendar' => [
                'day' => function($dt, $header, $body) use ($defStatus, $revStatus, $bDays) {
                    $status = !in_array($dt->format(), $bDays) ? $defStatus : $revStatus;
                    $html =
                        '<div class="day' . ($dt->format() == date('Y-m-d') ? ' today' : '') . '" data-status="' . $status . '" data-date="' . $dt->format() . '">'.
                            $header.
                            $body.
                        '</div>';
                    return $html;
                },
                'body' => function(Date $dayDt) use ($orderService, $guide) {
                    $orders = $orderService->getOrders([
                        'date_from' => $dayDt->format(),
                        'date_to'   => $dayDt->format(),
                        'guide_id'  => $guide->id(),
                    ]);
                    $orders->select()->order('days_count DESC');

                    return $this->calendarRow($dayDt, $orders, [
                        'attrs' => function($item, $attrs) {
                            return [
                                    'class' => $attrs['class'] . ' popup',
                                    'href' => $item->getProposalUrl(),
                                    'data-status' => $item->get('status') . '-' . $item->get('errors'),
                                ] + $attrs;
                        },
                    ]);
                }
            ]
        ])->calendar();
    }

   /* public function __invoke($calendar, $guide)
    {
        $defStatus = $guide->get('options')->calendar->status ?? 'free';
        $revStatus = $defStatus == 'free' ? 'busy' : 'free';

        $busyDays = \Guides\Admin\Model\GuideCalendar::getEntityCollection();
        $busyDays->select()->where
            ->greaterThanOrEqualTo('date', $calendar->getDt()->format('Y-m-01'))
            ->lessThanOrEqualTo('date', $calendar->getDt()->format('Y-m-31'))
            ->equalTo('busy', $revStatus)
            ->equalTo('depend', $guide->id());

        $bDays = [];
        foreach ($busyDays as $row) {
            $bDays[] = $row->get('date');
        }

        return $this->getView()->calendar($calendar, [
            'calendar' => [
                'day' => function($dt, $controls, $calendar) use ($defStatus, $revStatus, $bDays) {
                    $status = !in_array($dt->format(), $bDays) ? $defStatus : $revStatus;
                    $html =
                        '<div class="day' . ($dt->format() == date('Y-m-d') ? ' today' : '') . '" data-status="' . $status . '" data-date="' . $dt->format() . '">'.
                            $controls.
                            $calendar.
                        '</div>';
                    return $html;
                },
            ]
        ])->calendar();
    }*/
}