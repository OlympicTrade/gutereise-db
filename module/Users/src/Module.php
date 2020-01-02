<?php

namespace Users;

use Users\Common\Model\User;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $sharedEventManager = $eventManager->getSharedManager();

        /*$sharedEventManager->attach(AbstractActionController::class,
            MvcEvent::EVENT_DISPATCH, [$this, 'checkRights'], 100);*/
    }

    public function checkRights(MvcEvent $mvcEvent)
    {
        $routeParams = $mvcEvent->getRouteMatch()->getParams();

        $moduleStr = $routeParams['rights'] ?? $routeParams['module'] . '/' . $routeParams['section'];

        $user = User::getInstance();

        $user->checkRights($moduleStr, [
            'redirect' => true,
            //'admin'    => $routeParams['admin'] ?? false,
        ]);

        if($user->login()) {
            $user->set('online', date('Y-m-d H:i:s'))->save();
        }

        return true;
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}