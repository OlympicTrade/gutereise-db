<?php
namespace Drivers;

use Pipe\Mvc\Controller\ControllerFactory;
use Pipe\Mvc\Service\ServiceFactory;
use Zend\Router\Http;

return [
    'controllers' => [
        'factories' => [
            Admin\Controller\DriversController::class => ControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Admin\Service\DriversService::class => ServiceFactory::class,
        ],
    ],
    'router' => [
        'routes' => [

        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'drivers' => __DIR__ . '/../view',
        ],
    ],
];