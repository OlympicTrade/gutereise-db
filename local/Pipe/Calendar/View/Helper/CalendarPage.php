<?php
namespace Pipe\Calendar\View\Helper;

use Pipe\DateTime\Date;
use Pipe\Db\Entity\Entity;
use Zend\View\Helper\AbstractHelper;

abstract class CalendarPage extends AbstractHelper
{
    //Example
    /*
    public function __invoke($calendar, $itemsService)
    {
        return $this->getView()->calendar($calendar, [
            'calendar' => [
                'body' => function(Date $dayDt) use ($itemsService) {
                    $orders = $itemsService->getItems(['date_from' => $dayDt->format(), 'date_to' => $dayDt->format()]);

                    return $this->calendarRow($dayDt, $orders, [
                        'attrs' => function($item, $attrs) {
                            return [
                                'href' => $item->getUrl(),
                                'data-status' => $item->get('status') . '-' . $item->get('errors'),
                            ] + $attrs;
                        },
                    ]);
                }
            ]
        ])->calendar();
    }
    */

    protected $lvls = [];
    protected $daysCount = [];
    protected function calendarRow($dayDt, $items, $options)
    {
        $html =
            '<div class="body">'.
                '<div class="list">';

        $lvlsToFree = [];

        /** @var Entity $item */
        foreach($items as $item) {
            $rowClass = 'row';
            $itemId = $item->id();

            if(!isset($this->daysCount[$itemId])) {
                $this->daysCount[$itemId] = 0;
            } else {
                $this->daysCount[$itemId]++;
            }

            if(false === ($lvl = array_search($itemId, $this->lvls))) {
                if(false === ($lvl = array_search('free', $this->lvls))) {
                    $this->lvls[] = $itemId;
                    $lvl = count($this->lvls) - 1;
                } else {
                    $this->lvls[$lvl] = $itemId;
                }
            }

            if($item->get('date_from')->format() == $dayDt->format()) {
                $rowClass .= ' first';
            }

            if($item->get('date_to')->format() == $dayDt->format()) {
                $rowClass .= ' last';
                $lvlsToFree[] = $lvl;
            }

            $nameMargin = $this->daysCount[$itemId];
            if($this->daysCount[$itemId] > $dayDt->format('N')) {
                $nameMargin = $dayDt->format('N') - 1;
            }

            $attrs = [
                'style' => 'top: ' . (($lvl)*22+2) . 'px;',
                'class' => $rowClass,
                'title' => str_replace('"', "'", $item->get('name')),
                'href'  => '',
            ];

            $options = $options + [
                    'text' => function($item) {
                        return $item->get('name') ? $item->get('name') : 'Без названия';
                    },
                    'attrs' => function($item, $attrs) {
                        return [] + $attrs;
                    },
                ];

            $attrs = call_user_func($options['attrs'], $item, $attrs);
            $text = '<div class="name" style="left: calc(-' . ($nameMargin * 100) . '% + 4px)">' . call_user_func($options['text'], $item) . '</div>';

            $view = $this->getView();
            $html .= $view->htmlElement(($attrs['href'] ? 'a' : 'span'), $attrs, $text);
        }

        foreach($lvlsToFree as $lvl) {
            $this->lvls[$lvl] = 'free';
        }

        $html .=
                '</div>'.
            '</div>';

        return $html;
    }
}