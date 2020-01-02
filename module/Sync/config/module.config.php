<?php
namespace Sync;

use Pipe\Mvc\Controller\ControllerFactory;
use Pipe\Mvc\Service\ServiceFactory;

return [
    'controllers' => [
        'factories' => [
            Admin\Controller\SyncController::class   => ControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Admin\Service\PriceService::class => ServiceFactory::class,
            Admin\Service\SyncService::class => ServiceFactory::class,
        ],
    ],
];