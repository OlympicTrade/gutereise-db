<?php
namespace Orders\Admin\View\Helper;

use Pipe\Form\View\Helper\FormFactory;
use Zend\View\Helper\AbstractHelper;

class CalcDayForm extends AbstractHelper
{
    public function __invoke(\Orders\Admin\Form\CalcDayForm $form)
    {
        $factory = new FormFactory($this->getView(), $form);

        $view = $this->getView();

        $html =
            '<div class="proposal-settings">'.
                '<div class="switcher">Настройки ком. предложения</div>'.
                '<div class="body">'.
                    '<div class="row">'.
                        $factory->structure([
                            [
                                'type'   => 'panel',
                                'children' => [
                                    [
                                        ['width' => 50,
                                            'element' => '[proposal][place_start]',
                                            'html' =>
                                                '<span class="btn btn-2 btn-prefix" data-text="Встреча с гидом по адресу: "><i class="fas fa-map-marker-alt"></i></span>'.
                                                '<span class="btn btn-2 btn-prefix" data-text="Встреча с гидом в холле гостиницы "><i class="fas fa-building"></i></span>',
                                        ],
                                        [
                                            'width' => 50,
                                            'element' => '[proposal][place_end]',
                                            'html' =>
                                                '<span class="btn btn-2 btn-prefix" data-text="Окончание экскурсии по адресу: "><i class="fas fa-map-marker-alt"></i></span>'.
                                                '<span class="btn btn-2 btn-prefix" data-text="Окончание экскурсии у гостиницы "><i class="fas fa-building"></i></span>',
                                        ],
                                    ],
                                ],
                            ],
                        ]).
                        '<div class="cols">'.
                            '<div class="col-100">'.
                                '<div class="calc-block">' .
                                    '<div class="header">Доп расходы</div>'.
                                    '<div class="body">'.
                                        $factory->structure([['[extra][autocalc]']]).
                                        $view->smartList([
                                            'class'  => 'extra_list',
                                            'name'   => $form->getElName('[extra][list][_ID_]'),
                                            'header' => true,
                                            'fields' => [
                                                [
                                                    'width'  => '20',
                                                    'el'     => new \Zend\Form\Element\Text('[name]', ['label' => 'Название', 'options' => []]),
                                                ],
                                                [
                                                    'width'  => '55',
                                                    'el'     => new \Zend\Form\Element\Text('[proposal_name]', ['label' => 'Название для КП', 'options' => []]),
                                                ],
                                                [
                                                    'width'  => '12',
                                                    'el'     => new \Zend\Form\Element\Text('[income]', ['label' => 'Доход','options' => []]),
                                                ],
                                                [
                                                    'width'  => '13',
                                                    'el'     => new \Zend\Form\Element\Text('[outgo]', ['label' => 'Расход','options' => []]),
                                                ],
                                            ]
                                        ]).
                                    '</div>'.
                                '</div>'.
                            '</div>'.
                        '</div>'.
                        '<div class="cols">'.
                            '<div class="col-100">'.
                                '<div class="calc-block">' .
                                    '<div class="header">Расписание</div>'.
                                    '<div class="body">'.
                                        $factory->structure([['[proposal][timetable][autocalc]']]).
                                        $view->smartList([
                                            'class'  => 'proposal_timetable',
                                            'name'   => $form->getElName('[proposal][timetable][list][_ID_]'),
                                            'header' => false,
                                            'fields' => [
                                                [
                                                    'width'  => '20',
                                                    'el'     => new \Pipe\Form\Element\Time('[duration]', ['options' => []]),
                                                ],
                                                [
                                                    'width'  => '80',
                                                    'el'     => new \Zend\Form\Element\Text('[name]', ['options' => []]),
                                                ],
                                            ]
                                        ]).
                                    '</div>'.
                                '</div>'.
                            '</div>'.
                        '</div>'.
                        '<div class="cols">'.
                            '<div class="col-100">'.
                                '<div class="calc-block">' .
                                    '<div class="header">В стоимость включено</div>'.
                                    '<div class="body">'.
                                        $factory->structure([['[proposal][pricetable][autocalc]']]).
                                        $view->smartList([
                                            'class'  => 'proposal_pricetable',
                                            'name'   => $form->getElName('[proposal][pricetable][list][_ID_]'),
                                            'header' => false,
                                            'fields' => [
                                                [
                                                    'width'  => '100',
                                                    'el'     => new \Zend\Form\Element\Text('[name]', ['options' => []]),
                                                ],
                                            ]
                                        ]).
                                    '</div>'.
                                '</div>'.
                            '</div>'.
                        '</div>'.
                    '</div>'.
                '</div>'.
            '</div>'.

            $factory->structure([
                [
                    ['width' => 25, 'element' => 'date'],
                    ['width' => 25, 'element' => 'time'],
                    ['width' => 25, 'element' => 'transfer_time'],
                    ['width' => 25, 'element' => 'car_delivery_time'],
                ],
                [
                    ['width' => 25, 'element' => '[guides-calc][duration]'],
                    ['width' => 25, 'element' => '[guides-calc][count]'],
                    ['width' => 25, 'element' => 'transfer_id'],
                    ['width' => 25, 'element' => 'margin'],
                ],
                [
                    ['width' => 50, 'element' => 'transport_autocomplete'],
                    ['width' => 50, 'element' => 'museums_autocomplete'],
                ],
            ]).

            '<div class="museums"></div>'.
            '<div class="transports"></div>'.

            '<div>'.
                $view->formElement($form->get('day_id')).
            '</div>'.
            '<div class="day-details calc-details-box"></div>';

        return $html;
    }
}