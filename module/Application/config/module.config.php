<?php
namespace Application;

use Application\Admin\View\Helper\MetaTags;
use Application\Admin\View\Helper\SmartList;
use Pipe\Mvc\Controller\Admin\TableController;
use Pipe\Mvc\Controller\ControllerFactory;
use Pipe\Mvc\Service\Admin\TableService;
use Pipe\Mvc\Service\ServiceFactory;
use Zend\Router\Http;

use Application\View\Helper as AppVHelper;
use Pipe\View\Helper as PVHelper;
use Pipe\Form\View\Helper as PFVHelper;

return [
    'service_manager' => [
        'invokables' => [
            //'Module' => Common\Model\Module::class,
        ],
        'factories' => [
            Admin\Service\SearchService::class => ServiceFactory::class,
            \Pipe\Mvc\Service\Admin\SystemService::class => ServiceFactory::class,
            TableService::class        => ServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            TableController::class                      => ControllerFactory::class,
            Admin\Controller\IndexController::class     => ControllerFactory::class,
            Admin\Controller\SettingsController::class  => ControllerFactory::class,
            Admin\Controller\ErrorController::class     => ControllerFactory::class,
            Admin\Controller\MenuController::class      => ControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            //CompanyDetails::class => CompanyDetails::class,
        ]
    ],
    'view_helpers' => [
        'invokables' => [
            'htmlElement'       => PVHelper\HtmlElement::class,
            'tr'                => PVHelper\Translator::class,
            'declension'        => PVHelper\Declension::class,
            'price'             => PVHelper\Price::class,
            'date'              => PVHelper\Date::class,
            'breadcrumbs'       => PVHelper\Breadcrumbs::class,
            'url'               => PVHelper\Url::class,
            'calendar'          => \Pipe\Calendar\View\Helper\Calendar::class,
            'smartList'         => SmartList::class,

            'eCheckbox'         => PFVHelper\ECheckbox::class,
            'eCollection'       => PFVHelper\ECollection::class,
            'eSelect'           => PFVHelper\ESelect::class,
            'formCell'          => PFVHelper\formCell::class,

            'formFactory'       => PFVHelper\FormFactory::class,
            'adminNav'          => PVHelper\Admin\Nav::class,
            'adminSidebar'      => PVHelper\Admin\Sidebar::class,
            'adminTableList'    => PVHelper\Admin\TableList::class,
            'adminShortTable'   => PVHelper\Admin\ShortTable::class,
            'adminMetaTags'     => MetaTags::class,
            'adminUrl'          => PVHelper\Admin\Url::class,
            'adminContacts'     => PVHelper\Admin\Contacts::class,
            'adminPopupHeader'  => Admin\View\Helper\PopupHeader::class,
        ],
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type' => Http\Literal::class,
                'priority' => 1,
                'options'  => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Admin\Controller\IndexController::class,
                        'module'     => 'Application',
                        'section'    => 'Application',
                        'action'     => 'index',
                    ],
                ],
            ],
            'defaultShort' => [
                'type' => Http\Segment::class,
                'priority' => 10,
                'options'  => [
                    'route'    => '/:module[/:method][/:id]/',
                    'constraints' => [
                        'module'  => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'method'  => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'      => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Admin\Controller\IndexController::class,
                        'action'    => 'router',
                    ],
                ],
            ],
            'search' => [
                'type' => Http\Literal::class,
                'priority' => 500,
                'options' => [
                    'route'    => '/search/',
                    'defaults' => [
                        'controller' => Admin\Controller\IndexController::class,
                        'module'     => 'Application',
                        'section'    => 'Settings',
                        'model'      => 'Settings',
                        'action'     => 'search',
                    ],
                ],
            ],
            'settings' => [
                'type' => Http\Literal::class,
                'priority' => 500,
                'options' => [
                    'route'    => '/settings/',
                    'defaults' => [
                        'controller' => Admin\Controller\SettingsController::class,
                        'module'     => 'Application',
                        'section'    => 'Settings',
                        'model'      => 'Settings',
                        'action'     => 'index',
                    ],
                ],
            ],
            'error' => [
                'type' => Http\Literal::class,
                'priority' => 200,
                'options' => [
                    'route' => '/error/',
                    'defaults' => [
                        'controller' => Admin\Controller\ErrorController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            /*'application' => [
                'type' => Http\Literal::class,
                'priority' => 600,
                'options' => [
                    'route' => '/application',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'price' => [
                        'type'    => Http\Segment::class,
                        'priority' => 200,
                        'options' => [
                            'route'    => '-menu[/:action][/:id]/',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[a-zA-Z0-9_-]+',
                            ],
                            'defaults' => [
                                'controller' => Admin\Controller\MenuController::class,
                                'module'     => 'Application',
                                'section'    => 'Menu',
                                'model'      => 'Menu',
                                'action'     => 'index',
                            ],
                        ],
                    ],
                ],
            ],*/
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type'     => 'phpArray',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.php',
            ],
        ],
        'ru_RU' => [
            'validators' => [
                'file_type' => 'phpArray',
                'file_path' =>  __DIR__ . '/../language/Forms.php'
            ],
        ],
        'default' => 'ru_RU'
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/ajax'             => __DIR__ . '/../view/layout/admin/ajax.phtml',
            'layout/admin'            => __DIR__ . '/../view/layout/admin/default.phtml',
            'layout/layout'           => __DIR__ . '/../view/layout/error/default.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'pagination-slide'        => __DIR__ . '/../view/pagination/slide.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];
