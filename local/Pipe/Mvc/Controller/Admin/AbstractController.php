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

        $header = $module->name;

        $view = new ViewModel();
        $view->setVariables([
            'ajax'      => $this->isAjax(),
            'header'    => $header,
            'module'    => strtolower($module->module()),
            'section'   => strtolower($module->section()),
        ]);

        if($this->isAjax()) {
            $this->layout('layout/ajax');
            $this->layout()->setVariables([
                'header' => $header,
            ]);
            return $view;
        } else {
            $this->layout('layout/admin');
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