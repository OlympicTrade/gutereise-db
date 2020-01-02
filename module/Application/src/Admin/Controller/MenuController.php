<?php
namespace Application\Admin\Controller;

use Pipe\Mvc\Controller\Admin\TableController;

class MenuController extends TableController
{
    protected function getViewStructure() {
        return [
            'list' => [
                'table' => [
                    'sort'   => 'sort DESC',
                ],
            ],
            'edit' => [
                'form' => [
                    ['id'],
                    [
                        'type'   => 'panel',
                        'name'   => 'Основные параметры',
                        'children' => [
                            [
                                ['width' => 25, 'element' => 'name'],
                                ['width' => 25, 'element' => 'icon'],
                                ['width' => 25, 'element' => 'parent'],
                                ['width' => 25, 'element' => 'sort'],
                            ],
                        ],
                    ],
                    [
                        'type'   => 'panel',
                        'name'   => 'Адрес',
                        'children' => [
                            [
                                ['width' => 33, 'element' => 'url[module]'],
                                ['width' => 33, 'element' => 'url[section]'],
                                ['width' => 33, 'element' => 'url[action]'],
                            ],
                        ],
                    ],
                    [
                        'type'   => 'panel',
                        'name'   => 'Доступ',
                        'children' => [
                            [
                                ['width' => 50, 'element' => 'access[allow]'],
                                ['width' => 50, 'element' => 'access[deny]'],
                            ],
                        ],
                    ],
                    [
                        'type'   => 'panel',
                        'name'   => 'HTML',
                        'children' => [
                            [
                                ['width' => 100, 'element' => 'options[class]'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}