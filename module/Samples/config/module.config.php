<?php
namespace Samples;

use Pipe\Mvc\Controller\ControllerFactory;
use Pipe\Mvc\Service\ServiceFactory;
use Zend\Router\Http;

return [
    'controllers' => [
        'factories' => [
            Admin\Controller\SamplesController::class => ControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Admin\Service\SamplesService::class => ServiceFactory::class,
            Admin\Service\SystemService::class => ServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'sample' => Samples\View\Helper\Sample::class,
        ],
    ],
    'router' => [
        'routes' => [
            'samples' => [
                'type'    => Http\Segment::class,
                'priority' => 600,
                'options' => [
                    'route'    => '/samples[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'controller' => Admin\Controller\SamplesController::class,
                        'module'     => 'Samples',
                        'section'    => 'Samples',
                        'model'      => 'Sample',
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