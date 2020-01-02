<?php
namespace Museums\Admin\Controller;

use Museums\Admin\Model\Museum;
use Pipe\Mvc\Controller\Admin\TableController;
use Zend\View\Model\JsonModel;

class MuseumsController extends TableController
{
    protected function getViewStructure() {
        return [
            'list' => [
                'sidebar' => [
                    'preset' => 'list',
                ],
                'table' => [
                    'fields' => [
                        'name' => [
                            'header' => 'Название',
                        ],
                        'contacts' => [
                            'header' => 'Контакты',
                            'class'  => 'mb-hide',
                            'width'  => 400,
                            'filter' => function($model, $view) {
                                return $view->contacts($model);
                            }
                        ],
                    ],
                ],
            ],
            'edit' => [
                'sidebar' => [
                    'preset' => 'edit'
                ],
                'form' => [
                    ['id'],
                    [
                        'type'     => 'panel',
                        'name'     => 'Основные параметры',
                        'children' => [
                            [['width' => 100, 'element' => 'name']],
                            [['width' => 100, 'element' => 'contacts']],
                            [['width' => 100, 'element' => 'comment']],
                        ],
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Коммерческое предложение',
                        'children' => [[

                            ['width' => 50, 'element' => 'proposal_title'],
                            ['width' => 50, 'element' => 'proposal_title_plural'],
                        ]],
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Билеты',
                        'children' => [
                            [['width' => 100, 'element' => 'tickets']],
                        ],
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Экскурсионное обслуживание',
                        'children' => [
                            [['width' => 100, 'element' => 'guides']],
                        ],
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Прочие расходы',
                        'children' => [
                            [['width' => 100, 'element' => 'extra']],
                        ],
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Время работы',
                        'children' => [
                            [['width' => 100, 'element' => 'worktime']],
                        ],
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Выхдные дни',
                        'children' => [
                            [['width' => 100, 'element' => 'weekends']],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getInfoAction()
    {
        $id = $this->params()->fromPost('id');

        $museum = new Museum();
        $museum->id($id)->load();

        $name = str_replace(['"'], [''], $museum->get('name'));

        $worktime = 'c <b>' . substr($museum->get('worktime_from'), 0, 2) . '</b> до <b>' . substr($museum->get('worktime_to'), 0, 2) . '</b>';
        $weekends = '';
        array_walk($museum->get('weekends'), function ($val, $key) use (&$weekends) {
            $weekends .= '<span class="red">' . ( Date::$weekdaysShort[$val]) . '</span>, ';
        });

        if($weekends) {
            $worktime .= ' (' . rtrim($weekends, ', ') . ')';
        }

        return new JsonModel([
            'id'       => $museum->id(),
            'name'     => $name,
            'worktime' => $worktime,
        ]);
    }

    /**
     * @return \Museums\Admin\Service\MuseumsService
     */
    protected function getMuseumService()
    {
        return $this->getServiceManager()->get('Museums\Admin\Service\MuseumsService');
    }
}