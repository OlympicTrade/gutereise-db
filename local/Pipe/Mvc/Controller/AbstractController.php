<?php
namespace Pipe\Mvc\Controller;

use Application\Common\Model\Module;
use Zend\Mvc\Controller\AbstractActionController as ZendActionController;
use Zend\ServiceManager\ServiceManager;

abstract class AbstractController extends ZendActionController
{
    protected function isAjax()
    {
        return $this->getRequest()->isXmlHttpRequest();
    }

    protected function send404()
    {
        $response = $this->getResponse();
        $response->setStatusCode(404);
        $response->sendHeaders();

        return $response;
    }

    public function getViewHelper($helperName)
    {
        return $this->getServiceManager()->get('ViewHelperManager')->get($helperName);
    }

    /** @var ServiceManager */
    protected $serviceManager = null;

    public function setServiceManager($sm)
    {
        $this->serviceManager = $sm;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * @return Module
     */
    protected function module()
    {
        //return $this->getServiceManager()->get('Module');
        return Module::getInstance();
    }
}