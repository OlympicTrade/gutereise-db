<?php
namespace Transports;

use Pipe\Mvc\Controller\ControllerFactory;
use Pipe\Mvc\Service\ServiceFactory;
use Zend\Router\Http\Segment;
use Zend\Router\Http\Literal;

return [
    'controllers' => [
        'factories' => [
            Admin\Controller\TransportsController::class => ControllerFactory::class,
            Admin\Controller\TransfersController::class => ControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Admin\Service\TransportsService::class => ServiceFactory::class,
            Admin\Service\TransfersService::class => ServiceFactory::class,
            Admin\Service\SystemService::class => ServiceFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'transports' => [
                'type'    => Segment::class,
                'priority' => 500,
                'options' => [
                    'route'    => '/transports[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'controller' => Admin\Controller\TransportsController::class,
                        'module'     => 'Transports',
                        'section'    => 'Transports',
                        'model'      => 'Transport',
                        'action'     => 'index',
                    ],
                ],
            ],
            'transportsTransfers' => [
                'type'    => Segment::class,
                'priority' => 600,
                'options' => [
                    'route'    => '/transports/transfers[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'controller' => Admin\Controller\TransfersController::class,
                        'module'     => 'Transports',
                        'section'    => 'Transfers',
                        'model'      => 'Transfer',
                        'action'     => 'index',
                    ],
                ],
            ],
            'transportDrivers' => [
                'type'    => Segment::class,
                'priority' => 600,
                'options' => [
                    'route'    => '/transports/get-transport-drivers/',
                    'defaults' => [
                        'controller' => Admin\Controller\TransportsController::class,
                        'module'     => 'Transports',
                        'section'    => 'Transports',
                        'model'      => 'Transport',
                        'action'     => 'getTransportDrivers',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'transports' => __DIR__ . '/../view',
        ],
    ],
];