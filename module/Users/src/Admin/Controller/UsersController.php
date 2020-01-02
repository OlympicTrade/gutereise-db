<?php
namespace Users\Admin\Controller;

use Pipe\Mvc\Controller\Admin\TableController;

class UsersController extends TableController
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
                            'class' => 'mb-hide',
                            'filter' => function() {
                                return 'asdasd';
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
                        'children' => 'name',
                    ],
                ],
            ],
        ];
    }
}