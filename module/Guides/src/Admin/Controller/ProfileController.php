<?php
namespace Guides\Admin\Controller;

use Pipe\Mvc\Controller\Admin\TableController;
use Guides\Admin\Model\Guide;
use Users\Admin\Model\User;
use Zend\View\Model\JsonModel;

class ProfileController extends TableController
{
    public function indexAction()
    {
        $model = $this->getGuide();

        $editForm = $this->getEditForm()
            ->setOptions(['model' => $model])
            ->init();

        if($this->getRequest()->isPost()) {
            $editForm->setData($this->params()->fromPost());
            $editForm->setFilters();

            if($editForm->isValid()) {
                $data = $editForm->getData();
                $this->getService()->saveModel($data);

                return new JsonModel([
                    'status' => 1,
                    'data'   => $model->serializeArray()
                ]);
            } else {
                return new JsonModel([
                    'status' => 0,
                    'errors' => $editForm->getMessages()
                ]);
            }
        } else {
            $editForm->setData($model->serializeArray());
        }

        $view = $this->initLayout();

        $header = 'Профиль';
        if(!$this->isAjax()) {
            $this->layout()->getVariable('meta')->title = $header;
            $this->layout()->setVariable('header', $header);
        }

        $breadcrumbs = $this->layout()->getVariable('breadcrumbs');
        $breadcrumbs[] = ['url' => '/', 'name' => $header];
        $this->layout()->setVariable('breadcrumbs', $breadcrumbs);

        $view->setVariables([
            'form'      => $editForm,
            'guide'     => $model,
        ]);

        return $view;
    }

    /**
     * @return \Guides\Admin\Model\Guide
     * @throws \Exception
     */
    protected function getGuide()
    {
        $guide = new Guide();
        $guide->select()
            ->where(['user_id' => User::getInstance()->id()]);

        if(!$guide->load()) {
            throw new \Exception('Гид не найден');
        }

        return $guide->load();
    }

    /**
     * @return /Guides/Service/GuidesService
     */
    protected function getService()
    {
        return parent::getService();
    }
}