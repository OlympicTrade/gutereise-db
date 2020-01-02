<?php
namespace Guides;

use Pipe\Mvc\Controller\ControllerFactory;
use Pipe\Mvc\Service\ServiceFactory;
use Zend\Router\Http;

return [
    'controllers' => [
        'factories' => [
            Admin\Controller\GuidesController::class   => ControllerFactory::class,
            Admin\Controller\ProfileController::class  => ControllerFactory::class,
            Admin\Controller\CalendarController::class => ControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Admin\Service\GuidesService::class => ServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'guidesCalendarPage' => \Guides\View\Helper\GuidesCalendarPage::class,
        ],
    ],
    'router' => [
        'routes' => [
            'guides' => [
                'type' => Http\Literal::class,
                'priority' => 600,
                'options' => [
                    'route' => '/guides',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'guides' => [
                        'type'    => Http\Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route'    => '[/:action][/:id]/',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[a-zA-Z0-9_-]+',
                            ],
                            'defaults' => [
                                'controller' => Admin\Controller\GuidesController::class,
                                'module'     => 'Guides',
                                'section'    => 'Guides',
                                'model'      => 'Guide',
                                'action'     => 'index',
                            ],
                        ],
                    ],
                    'profile' => [
                        'type'    => Http\Segment::class,
                        'priority' => 200,
                        'options' => [
                            'route'    => '/profile[/:action][/:id]/',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[a-zA-Z0-9_-]+',
                            ],
                            'defaults' => [
                                'controller' => Admin\Controller\ProfileController::class,
                                'module'     => 'Guides',
                                'section'    => 'Profile',
                                'model'      => 'Guide',
                                'action'     => 'index',
                            ],
                        ],
                    ],
                    'calendar' => [
                        'type'    => Http\Segment::class,
                        'priority' => 200,
                        'options' => [
                            'route'    => '-calendar[/:action][/:id]/',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[a-zA-Z0-9_-]+',
                            ],
                            'defaults' => [
                                'controller' => Admin\Controller\CalendarController::class,
                                'rights'     => 'guides/guides, guides/calendar',
                                'module'     => 'Guides',
                                'section'    => 'Calendar',
                                'model'      => 'Guide',
                                'action'     => 'index',
                            ],
                        ],
                    ],
                    'price' => [
                        'type'    => Http\Literal::class,
                        'priority' => 200,
                        'options' => [
                            'route'    => '-price/',
                            'defaults' => [
                                'controller' => Admin\Controller\GuidesController::class,
                                'rights'     => 'guides/guides',
                                'module'     => 'Guides',
                                'section'    => 'Guides',
                                'model'      => 'Guide',
                                'action'     => 'price',
                            ],
                        ],
                    ],
                ],
            ],
            'guidesByLang' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/guides/get-guides-by-lang/',
                    'defaults' => [
                        'controller' => Admin\Controller\GuidesController::class,
                        'module'     => 'Guides',
                        'section'    => 'Guides',
                        'model'      => 'Guides',
                        'action'     => 'getGuidesByLang',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'guides' => __DIR__ . '/../view',
        ],
    ],
];