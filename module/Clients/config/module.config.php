<?php
namespace Clients;

use Pipe\Mvc\Controller\ControllerFactory;
use Pipe\Mvc\Service\ServiceFactory;
use Zend\Router\Http;

return [
    'controllers' => [
        'factories' => [
            Admin\Controller\ClientsController::class => ControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            //Admin\Service\ClientsService::class => ServiceFactory::class,
        ],
    ],
    'router' => [

    ],
    'view_manager' => [
        'template_path_stack' => [
            'clients' => __DIR__ . '/../view',
        ],
    ],
];