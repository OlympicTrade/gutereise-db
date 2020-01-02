<?php
namespace Orders;

use Pipe\Mvc\Controller\ControllerFactory;
use Pipe\Mvc\Service\ServiceFactory;
use Zend\Router\Http\Segment;
use Zend\Router\Http\Literal;
use Orders\Admin\View\Helper as OVHelper;

return [
    'controllers' => [
        'factories' => [
            Admin\Controller\OrdersController::class  => ControllerFactory::class,
            Admin\Controller\CalcController::class    => ControllerFactory::class,
            Admin\Controller\BalanceController::class => ControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Admin\Service\GCalendarService::class => ServiceFactory::class,
            Admin\Service\OrdersService::class    => ServiceFactory::class,
            Admin\Service\CalcService::class      => ServiceFactory::class,
            Admin\Service\ProposalService::class  => ServiceFactory::class,
            Admin\Service\SystemService::class    => ServiceFactory::class,
            Admin\Service\BalanceService::class   => ServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'orderDayForm'        => OVHelper\OrderDayForm::class,
            'calcDayForm'         => OVHelper\CalcDayForm::class,
            'calcHotelRooms'      => OVHelper\CalcHotelRooms::class,
            'balanceList'         => OVHelper\BalanceList::class,
            'orderCalendarPage'   => OVHelper\OrderCalendarPage::class,
            'balanceCalendarPage' => OVHelper\BalanceCalendarPage::class,
        ],
    ],
    'router' => [
        'routes' => [
            'calc' => [
                'type'    => Segment::class,
                'priority' => 600,
                'options' => [
                    'route'    => '/calc[/:action]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Admin\Controller\CalcController::class,
                        'module'     => 'Orders',
                        'section'    => 'Orders',
                        'action'     => 'calc',
                    ],
                ],
            ],
            'balance' => [
                'type'    => Segment::class,
                'priority' => 600,
                'options' => [
                    'route'    => '/balance[/:action]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Admin\Controller\BalanceController::class,
                        'module'     => 'Orders',
                        'section'    => 'Balance',
                        'action'     => 'index',
                    ],
                ],
            ],
            'orders' => [
                'type' => Literal::class,
                'priority' => 600,
                'options' => [
                    'route' => '/orders',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type'    => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route'    => '[/:action][/:id]/',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[a-zA-Z0-9_-]+',
                            ],
                            'defaults' => [
                                'controller' => Admin\Controller\OrdersController::class,
                                'module'     => 'Orders',
                                'section'    => 'Orders',
                                'model'      => 'Order',
                                'action'     => 'index',
                            ],
                        ],
                    ],
                    'proposal' => [
                        'type'    => Segment::class,
                        'priority' => 200,
                        'options' => [
                            'route'    => '/proposal[/:id]/',
                            'constraints' => [
                                'id'     => '[a-zA-Z0-9_-]+',
                            ],
                            'defaults' => [
                                'controller' => Admin\Controller\OrdersController::class,
                                'resources'  => 'orders-orders, guides-profile',
                                'module'     => 'Orders',
                                'section'    => 'Orders',
                                'model'      => 'Order',
                                'action'     => 'proposal',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'orders' => __DIR__ . '/../view',
        ],
    ],
];