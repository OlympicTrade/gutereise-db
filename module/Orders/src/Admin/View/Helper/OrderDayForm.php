<?php
namespace Orders\Admin\View\Helper;

use Drivers\Admin\Model\Driver;
use Excursions\Admin\Model\ExcursionDay;
use Museums\Admin\Model\Museum;
use Orders\Admin\Model\Order;
use Orders\Admin\Model\OrderDay;
use Pipe\Form\View\Helper\FormFactory;
use Transports\Admin\Model\Transport;
use Zend\View\Helper\AbstractHelper;

class OrderDayForm extends AbstractHelper
{
    public function __invoke(OrderDay $day)
    {
        $form = $day->getForm();
        $view = $this->getView();

        $formFactory = new FormFactory($view, $form);

        $html =
            '<div class="day-details calc-details-box"></div>';

        if($day->get('day_id') && $exDay = (new ExcursionDay(['id' => $day->get('day_id')]))->load()) {
            $excursion = $exDay->getExcursion('excursion');
            $html .=
                '<div class="day-excursion">' .
                    '<a class="btn sm popup" href="' . $excursion->getUrl() . '">' . $excursion->get('name') . '</a>'.
                '</div>';
        }

        $html .=
        '<div class="tabs order-day-tabs">'.
            '<div class="tabs-header">'.
                '<div class="tab" data-tab="main">Расписание</div>'.
                '<div class="tab" data-tab="guides">Гиды</div>'.
                '<div class="tab" data-tab="transports">Транспорт</div>'.
                '<div class="tab" data-tab="museums">Музеи</div>'.
                '<div class="tab" data-tab="extra">Доп. расходы</div>'.
                '<div class="tab blue btn day-recalc" data-did="' . $day->id() . '">'.
                    '<i class="fas fa-calculator"></i>'.
                '</div>'.
            '</div>'.
            '<div class="tabs-body">'.
                /*'<div class="tab" data-tab="main">'.
                $formFactory->structure([
                    ['id'],
                    [
                        ['width' => 20, 'element' => 'date'],
                        ['width' => 20, 'element' => 'time'],
                        ['width' => 20, 'element' => 'transfer_time'],
                        ['width' => 20, 'element' => 'car_delivery_time'],
                        ['width' => 20, 'element' => 'duration'],
                    ],
                    [
                        ['width' => 20, 'element' => 'margin'],
                        ['width' => 80, 'element' => 'transfer_id'],
                    ],
                    [
                        'type'   => 'panel',
                        'children' => [
                            [
                                ['width' => 50,
                                    'element' => '[options][proposal][place_start]',
                                    'html' =>
                                        '<span class="btn btn-2 btn-prefix" data-text="Встреча с гидом по адресу: "><i class="fas fa-map-marker-alt"></i></span>'.
                                        '<span class="btn btn-2 btn-prefix" data-text="Встреча с гидом в холле гостиницы "><i class="fas fa-building"></i></span>',
                                ],
                                [
                                    'width' => 50,
                                    'element' => '[options][proposal][place_end]',
                                    'html' =>
                                        '<span class="btn btn-2 btn-prefix" data-text="Окончание экскурсии по адресу: "><i class="fas fa-map-marker-alt"></i></span>'.
                                        '<span class="btn btn-2 btn-prefix" data-text="Окончание экскурсии у гостиницы "><i class="fas fa-building"></i></span>',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type'   => 'panel',
                        'name'   => 'Расписание',
                        'attrs'  => ['data-anchor' => 'timetable'],
                        'children' => [
                            '[options][proposal][timetable][autocalc]', '[timetable]'
                        ],
                    ],
                    [
                        'type'   => 'panel',
                        'name'   => 'В стоимость включено',
                        'attrs'  => ['data-anchor' => 'pricetable'],
                        'children' => [
                            '[options][proposal][pricetable][autocalc]', '[pricetable]'
                        ],
                    ],
                ]).
            '</div>'.
            '<div class="tab" data-tab="guides">'.
                $formFactory->structure([[
                    'type'   => 'panel',
                    'children' => [
                        [
                            'type' => 'html',
                            'html' => '<div class="errors std-errors" data-name="guides"></div>',
                        ],
                        '[options][guides][autocalc]', '[guides]'
                    ],
                ]]).
            '</div>'.
            '<div class="tab" data-tab="museums">'.
                $formFactory->structure([[
                    'type'   => 'panel',
                    'children' => [
                        [
                            'type' => 'html',
                            'html' => '<div class="errors std-errors" data-name="museums"></div>',
                        ],
                        '[options][museums][autocalc]', '[museums]'
                    ],
                ]]).
            '</div>'.
            '<div class="tab" data-tab="transports">'.
                $formFactory->structure([[
                    'type'   => 'panel',
                    'children' => [
                        [
                            'type' => 'html',
                            'html' => '<div class="errors std-errors" data-name="transports"></div>',
                        ],
                        '[options][transports][autocalc]', '[transports]'
                    ],
                ]]).
            '</div>'.
            '<div class="tab" data-tab="extra">'.
                $formFactory->structure([[
                    'type'   => 'panel',
                    'children' => [
                        [
                            'type' => 'html',
                            'html' => '<div class="errors std-errors" data-name="extra"></div>',
                        ],
                        '[options][extra][autocalc]', '[extra]'
                    ],
                ]]).
            '</div>'.*/
        '</div>';

        $html .=
            '</div>';

        return '';
        return $html;
    }
}