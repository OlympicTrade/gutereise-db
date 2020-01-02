<?php
namespace Documents;

use Documents\Admin\Controller\DocumentsController;
use Pipe\Mvc\Controller\ControllerFactory;
use Pipe\Mvc\Service\ServiceFactory;

return [
    'controllers' => [
        'factories' => [
            DocumentsController::class => ControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Admin\Service\DocumentsService::class => ServiceFactory::class,
x        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'documents' => __DIR__ . '/../view',
        ],
    ],
];