<?php
namespace Managers\Admin\Controller;

use Pipe\Mvc\Controller\Admin\TableController;

class ManagersController extends TableController
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
                            ['type' => 'preset', 'name' => 'basic'],
                        ],
                    ],
                ],
            ],
        ];
    }
}