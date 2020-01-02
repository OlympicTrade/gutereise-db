<?php
namespace Managers;

use Pipe\Mvc\Controller\ControllerFactory;

return [
    'controllers' => [
        'factories' => [
            Admin\Controller\ManagersController::class    => ControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'managers' => __DIR__ . '/../view',
        ],
    ],
];