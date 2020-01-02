<?php
namespace Excursions\Admin\View\Helper;

use Excursions\Admin\Model\ExcursionDay;
use Pipe\Form\View\Helper\FormFactory;
use Zend\View\Helper\AbstractHelper;

class ExcursionDayForm extends AbstractHelper
{
    public function __invoke(ExcursionDay $day)
    {
        $form = $day->getForm();
        $view = $this->getView();

        $formFactory = new FormFactory($view, $form);

        $html = $formFactory->structure([
            ['id'],
            [
                ['width' => 25, 'element' => 'transfer_time'],
                ['width' => 25, 'element' => 'car_delivery_time'],
                ['width' => 25, 'element' => 'min_time'],
                ['width' => 25, 'element' => 'max_time'],
            ],
            [
                ['width' => 100, 'element' => 'transfer_id'],
            ],
            [
                'type'   => 'panel',
                'name'   => 'Коммерческое предложение',
                'children' => [
                    [
                        [
                            'width' => 50,
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
                        [
                            'type' => 'html',
                            'html' =>
                                $view->formCell($form->get('[options][proposal][price]')).
                                '<div class="proposal-settings">'.
                                    '<span class="label">Авторассчет:</span>'.
                                    $view->formCell($form->get('[options][proposal][price_guides]')).
                                    $view->formCell($form->get('[options][proposal][price_museums]')).
                                    $view->formCell($form->get('[options][proposal][price_transport]')).
                                '</div>'
                        ],
                ],
            ],
            [
                'type'   => 'panel',
                'name'   => 'Расписание',
                'children' => ['timetable'],
            ],
            [
                'type'   => 'panel',
                'name'   => 'Кол-во гидов',
                'children' => ['guides'],
            ],
            [
                'type'   => 'panel',
                'name'   => 'Музеи',
                'children' => ['museums'],
            ],
            [
                'type'   => 'panel',
                'name'   => 'Транспорт',
                'children' => ['transport'],
            ],
            [
                'type'   => 'panel',
                'name'   => 'Прочие расходы',
                'children' => ['extra'],
            ],
        ]);

        /*$html =
            $view->formElement($form->get('id')) .
            $view->formRowset([
                [
                    ['width' => 25, 'element' => 'transfer_time'],
                    ['width' => 25, 'element' => 'car_delivery_time'],
                    ['width' => 25, 'element' => 'min_time'],
                    ['width' => 25, 'element' => 'max_time'],
                ],
                [
                    ['width' => 100, 'element' => 'transfer_id'],
                ],
            ], $form)
        ;

        $html .=
        '<fieldset>'.
            '<legend>Коммерческое предложение</legend>'.
            '<div class="row">'.
                '<div class="cols">'.
                    '<div class="col-50">'.
                        $view->formRow($form->get('[options][proposal][place_start]'), [
                            'html' =>
                                '<span class="btn sm blue btn-2 btn-prefix" data-text="Встреча с гидом по адресу: "><i class="far fa-map-marker-alt"></i></span>'.
                                '<span class="btn sm blue btn-2 btn-prefix" data-text="Встреча с гидом в холле гостиницы "><i class="far fa-building"></i></span>',
                        ]).
                    '</div>'.
                    '<div class="col-50">'.
                        $view->formRow($form->get('[options][proposal][place_end]'), [
                            'html' =>
                                '<span class="btn sm blue btn-2 btn-prefix" data-text="Окончание экскурсии по адресу: "><i class="far fa-map-marker-alt"></i></span>'.
                                '<span class="btn sm blue btn-2 btn-prefix" data-text="Окончание экскурсии у гостиницы "><i class="far fa-building"></i></span>',
                        ]).
                    '</div>'.
                '</div>'.
                '<div class="cols">'.
                    '<div class="col-100">'.
                        $view->formRow($form->get('[options][proposal][price]')).

                        '<div class="proposal-settings">'.
                            '<span class="label">Авторассчет:</span>'.
                            $view->formRow($form->get('[options][proposal][price_guides]')).
                            $view->formRow($form->get('[options][proposal][price_museums]')).
                            $view->formRow($form->get('[options][proposal][price_transport]')).
                        '</div>'.
                    '</div>'.
                '</div>'.
            '</div>'.
        '</fieldset>';

        $timetable = $day->plugin('timetable');
        $timetable->select()
            ->order('foreigners')
            ->order('tourists_from');

        $html .=
            '<fieldset>'.
                '<legend>Расписание</legend>'.
                $view->formElement($form->get('[timetable]')).
            '</fieldset>';

        $html .=
            '<fieldset>'.
                '<legend>Кол-во гидов</legend>'.
                $view->formElement($form->get('[guides]')).
            '</fieldset>';

        $html .=
            '<fieldset>'.
                '<legend>Музеи</legend>'.
                $view->formElement($form->get('[museums]')).
            '</fieldset>';

        $html .=
            '<fieldset>'.
                '<legend>Транспорт</legend>'.
                $view->formElement($form->get('[transport]')).
            '</fieldset>';

        $html .=
            '<fieldset>'.
                '<legend>Прочие расходы</legend>'.
                $view->formElement($form->get('[extra]')).
            '</fieldset>';*/

        return $html;
    }
}