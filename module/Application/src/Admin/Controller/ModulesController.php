<?php
namespace Application\Admin\Controller;

use Pipe\Mvc\Controller\Admin\TableController;

class ModulesController extends TableController
{
    protected function getViewStructure() {
        return [
            'edit' => [
                'form' => [
                    ['id'],
                    [
                        'type'   => 'panel',
                        'name'   => 'Основные параметры',
                        'children' => [
                            [
                                ['width' => 25, 'element' => 'name'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}