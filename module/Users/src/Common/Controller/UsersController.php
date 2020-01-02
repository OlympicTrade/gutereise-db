<?php
namespace Users\Common\Controller;

use Pipe\Mvc\Controller\Admin\TableController;
use Users\Admin\Form\LoginForm;
use Users\Admin\Model\User;
use Zend\View\Model\ViewModel;

class UsersController extends TableController
{
    public function accessDeniedAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('users/users/access-denied');
    }
    public function logoutAction()
    {
        User::getInstance()->logout();

        return $this->redirect()->toUrl('/login/');
    }

    public function loginAction()
    {
        if(User::getInstance()->login()) {
            return $this->redirect()->toUrl('/');
        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);

        $form = new LoginForm();

        $formMsg = '';

        if ($this->getRequest()->isPost()) {
            $form->setFilters();
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $formData = $form->getData();

                $result = User::getInstance()->auth($formData['login'], $formData['password']);

                if($result['status']) {
                    $this->redirect()->toUrl('/');
                } else {
                    $formMsg = User::$errorsText[$result['error']];
                }
            } else {
                $formMsg = 'Форма заполнена неверно';
            }
        }

        $viewModel->setVariables([
            'form'      => $form,
            'formMsg'   => $formMsg
        ]);

        return $viewModel;
    }

    /**
     * @return \Users\Service\UsersService
     */
    protected function getUserService()
    {
        return $this->getServiceManager()->get('Users\Service\UsersService');
    }

    /**
     * @return \Users\Service\SystemService
     */
    protected function getSystemService()
    {
        return $this->getServiceManager()->get('Users\Service\SystemService');
    }
}