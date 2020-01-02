<?php
namespace Excursions;

use Excursions\Admin\View\Helper\ExcursionDayForm;
use Pipe\Mvc\Controller\ControllerFactory;
use Pipe\Mvc\Service\ServiceFactory;
use Zend\Router\Http\Segment;
use Zend\Router\Http\Literal;

return [
    'controllers' => [
        'factories' => [
            Admin\Controller\ExcursionsController::class => ControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Admin\Service\ExcursionsService::class => ServiceFactory::class,
            Admin\Service\SystemService::class => ServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'excursionDayForm' => ExcursionDayForm::class,
        ],
    ],
    'router' => [
        'routes' => [
            'excursions' => [
                'type'    => Segment::class,
                'priority' => 600,
                'options' => [
                    'route'    => '/excursions[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'controller' => Admin\Controller\ExcursionsController::class,
                        'module'     => 'Excursions',
                        'section'    => 'Excursions',
                        'model'      => 'Excursion',
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'excursions' => __DIR__ . '/../view',
        ],
    ],
];