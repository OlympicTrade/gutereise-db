<?php

namespace Transports;

use Transports\Admin\Service\SystemService;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface
{
    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Transports\Admin\Service\TransfersService'   => 'Transports\Admin\Service\TransfersService',
                'Transports\Admin\Service\TransportsService'  => 'Transports\Admin\Service\TransportsService',
                'Transports\Admin\Model\Transport'            => 'Transports\Admin\Model\Transport',
                'Transports\Admin\Model\Transfer'             => 'Transports\Admin\Model\Transfer',
            ),
            'factories' => array(
                'Transports\Admin\Service\SystemService' => function ($sm) {
                    $service = new SystemService();
                    $service->setTable('transports');
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