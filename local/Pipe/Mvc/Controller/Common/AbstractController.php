<?php

namespace Pipe\Mvc\Controller\Common;

use Contacts\Common\Model\Contacts;
use Zend\View\Model\ViewModel;

abstract class AbstractController extends \Pipe\Mvc\Controller\AbstractController
{
    public function initLayout($url = null)
    {
        $this->layout('layout/common');

        $header = '';

        $view = new ViewModel();
        $view->setVariables([
            'ajax'      => $this->isAjax(),
            'header'    => $header,
        ]);

        if($this->isAjax()) {
            $view->setTerminal(true);
            return $view;
        }

        $this->layout()->setVariables([
            'meta'        => (array) [
                'title'       => $header,
                'description' => '',
            ],
            'header'      => $header,
            'contacts'    => Contacts::getInstance(),
        ]);

        return $view;
    }
}