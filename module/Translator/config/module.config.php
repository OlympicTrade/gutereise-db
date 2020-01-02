<?php
namespace Translator;

use Pipe\Mvc\Controller\ControllerFactory;
//use Zend\Router\Http;

return [
    'controllers' => [
        'factories' => [
            Admin\Controller\TranslatorController::class => ControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [

        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'translator' => __DIR__ . '/../view',
        ],
    ],
];