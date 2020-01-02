<?php

namespace Excursions;

use Excursions\Service\SystemService;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface
{
    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'ExcursionDayForm' => 'Excursions\View\Helper\ExcursionDayForm',
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Excursions\Service\ExcursionsService'  => 'Excursions\Service\ExcursionsService',
                'Excursions\Admin\Model\Excursion'            => 'Excursions\Admin\Model\Excursion',
            ),
            'factories' => array(
                'Excursions\Service\SystemService' => function ($sm) {
                    $service = new SystemService();
                    $service->setTable('excursions');
                    return $service;
                }
            )
        );
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__           => __DIR__ . '/src/' . __NAMESPACE__,
                )
            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}