<?php

namespace Pipe\Mvc\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var DefaultController $controller */
        $controller = new $requestedName();
        $controller->setServiceManager($container);

        return $controller;
    }
}