<?php
namespace Hotels;

use Hotels\Admin\View\Helper\HotelRoomForm;
use Pipe\Mvc\Controller\ControllerFactory;
use Pipe\Mvc\Service\ServiceFactory;
use Zend\Router\Http\Segment;

return [
    'controllers' => [
        'factories' => [
            Admin\Controller\HotelsController::class => ControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Admin\Service\HotelsService::class => ServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'hotelRoomForm' => HotelRoomForm::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'hotels' => __DIR__ . '/../view',
        ],
    ],
];