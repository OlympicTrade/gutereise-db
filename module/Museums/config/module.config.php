<?php
namespace Museums;

use Pipe\Mvc\Controller\ControllerFactory;
use Pipe\Mvc\Service\ServiceFactory;
use Zend\Router\Http\Segment;

return [
    'controllers' => [
        'factories' => [
            Admin\Controller\MuseumsController::class => ControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Admin\Service\MuseumsService::class => ServiceFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'samples' => [
                'type'    => Segment::class,
                'priority' => 600,
                'options' => [
                    'route'    => '/museums[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'controller' => Admin\Controller\MuseumsController::class,
                        'module'     => 'Museums',
                        'section'    => 'Museums',
                        'model'      => 'Museum',
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'samples' => __DIR__ . '/../view',
        ],
    ],
];