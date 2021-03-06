<?php

namespace Pipe\Mvc\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var ModuleService $service */
        $service = new $requestedName();
        $service->setServiceManager($container);

        return $service;
    }
}