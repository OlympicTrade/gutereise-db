<?php

namespace Documents;

use Documents\Service\SystemService;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface
{
    public function getViewHelperConfig()
    {
        return [
            'invokables' => [
                //'DocumentsHelper'  => 'Documents\View\Helper\DocumentsHelper',
            ],
        ];
    }
    
    public function getServiceConfig()
    {
        return [
            'invokables' => [
                'Documents\Service\DocumentsService'  => 'Documents\Service\DocumentsService',
                'Documents\Admin\Model\Document'            => 'Documents\Admin\Model\Document',
            ],
            'factories' => [
                'Documents\Service\SystemService' => function ($sm) {
                    $service = new SystemService();
                    $service->setTable('documents');
                    return $service;
                },
            ],
        ];
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__           => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}