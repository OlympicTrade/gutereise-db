<?php
namespace Application\Admin\Controller;

use Pipe\Mvc\Controller\Admin\TableController;
use Users\Common\Model\User;
use Pipe\Mvc\Controller\AbstractController;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractController
{
    public function routerAction()
    {
        $module = $this->module();
        $params = $this->params()->fromRoute();

        $controller = $module->module() . '\\Admin\\Controller\\' . $module->section() .'Controller';
        $action = $params['method'] ?? 'index';

        if(!class_exists($controller)) {
            $controller = TableController::class;
        }

        return $this->forward()->dispatch($controller, ['action'  => $action, 'id' => $params['id']]);
    }

    public function indexAction()
    {
        $user = User::getInstance();
        return $this->redirect()->toUrl('/orders/');
        /*switch ($user->role()->name) {
            case 'Гид':
                return $this->redirect()->toUrl('/guides/calendar/');
            default:
                return $this->redirect()->toUrl('/orders/');
        }*/
    }

    public function searchAction()
    {
        $query = trim($this->params()->fromQuery('query'));

        if($this->getRequest()->isXmlHttpRequest()) {
            $resp = $this->getSearchService()->autocomplete($query);
            return new JsonModel($resp);
        }

        $result = $this->getSearchService()->searchData($query);

        if(count($result) == 1 && count($result[0]['data']) == 1) {
            return $this->redirect()->toUrl('/' . $result[0]['info']['module'] . '/edit/' . $result[0]['data']->current()['id'] . '/');
        }

        $view = $this->initLayout();
        $view->setVariables([
            'query'   => $query,
            'results' => $this->getSearchService()->searchData($query),
        ]);
        ;
        return $view;
    }

    /**
     * @return \Application\Admin\Service\SearchService
     */
    protected function getSearchService()
    {
        return $this->getServiceManager()->get('Application\Admin\Service\SearchService');
    }
}
