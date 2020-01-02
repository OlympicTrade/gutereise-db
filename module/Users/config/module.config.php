<?php
namespace Users;

use Pipe\Mvc\Controller\ControllerFactory;
use Zend\Router\Http;

return [
    'controllers' => [
        'factories' => [
            Common\Controller\UsersController::class   => ControllerFactory::class,
            Admin\Controller\UsersController::class    => ControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'access-denied' => [
                'type'    => Http\Literal::class,
                'priority' => 600,
                'options' => [
                    'route'    => '/access-denied/',
                    'defaults' => [
                        'controller' => Admin\Controller\UsersController::class,
                        'rights'     => 'login',
                        'module'     => 'Users',
                        'section'    => 'Users',
                        'model'      => 'User',
                        'action'     => 'access-denied',
                    ],
                ],
            ],
            'login' => [
                'type'    => Http\Literal::class,
                'priority' => 600,
                'options' => [
                    'route'    => '/login/',
                    'defaults' => [
                        'controller' => Admin\Controller\UsersController::class,
                        'rights'     => 'login',
                        'module'     => 'Users',
                        'section'    => 'Users',
                        'model'      => 'User',
                        'action'     => 'login',
                    ],
                ],
            ],
            'logout' => [
                'type'    => Http\Literal::class,
                'priority' => 600,
                'options' => [
                    'route'    => '/logout/',
                    'defaults' => [
                        'controller' => Admin\Controller\UsersController::class,
                        'rights'     => 'login',
                        'module'     => 'Users',
                        'section'    => 'Users',
                        'model'      => 'User',
                        'action'     => 'logout',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'users' => __DIR__ . '/../view',
        ],
    ],
];