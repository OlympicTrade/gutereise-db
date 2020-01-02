<?php

namespace Pipe\Mvc\Controller\Admin;

use Zend\Json\Json;
use Zend\View\Model\ViewModel;

abstract class AbstractController extends \Pipe\Mvc\Controller\AbstractController
{
    public function initLayout($url = null)
    {
        $module = $this->module();

        $this->layout('layout/admin');

        $header = 'База данных';

        $view = new ViewModel();
        $view->setVariables([
            'ajax'      => $this->isAjax(),
            'header'    => $header,
            'module'    => strtolower($module->module()),
            'section'   => strtolower($module->section()),
        ]);

        if($this->isAjax()) {
            $view->setTerminal(true);
            return $view;
        }

        try {
            $options = Json::decode($_COOKIE['template']);
        } catch (\Exception $e) {
            $options = (object) [
                'nav' => (object) [
                    'main'    => 'wire',
                    'module'  => 'wire',
                ],
            ];
        }

        $this->layout()->setVariables([
            'meta'        => (array) [
                'title'       => $header,
                'description' => '',
            ],
            'header'      => $header,
            'module'      => strtolower($module->module()),
            'section'     => strtolower($module->section()),
            'template'    => $options,
        ]);

        return $view;
    }
}