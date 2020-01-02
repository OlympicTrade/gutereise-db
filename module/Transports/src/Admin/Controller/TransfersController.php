<?php
namespace Transports\Admin\Controller;

use Pipe\Mvc\Controller\Admin\TableController;

class TransfersController extends TableController
{

    protected function getViewStructure() {
        return [
            'edit' => [
                'form' => [
                    [
                        'type'     => 'panel',
                        'name'     => 'Основные параметры',
                        'children' => [
                            [
                                ['width' => 33, 'element' => 'name'],
                                ['width' => 33, 'element' => 'duration'],
                            ],
                        ],
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Расценки гидов',
                        'children' => ['guides'],
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Расценки на транспорт',
                        'children' => ['transport'],
                    ],
                ],
            ],
        ];
    }
}