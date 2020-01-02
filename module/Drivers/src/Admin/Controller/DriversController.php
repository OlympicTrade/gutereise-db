<?php
namespace Drivers\Admin\Controller;

use Pipe\Mvc\Controller\Admin\TableController;
use Zend\View\Model\JsonModel;

class DriversController extends TableController
{
    protected function getViewStructure() {
        return [
            'edit' => [
                'form' => [
                    ['id'],
                    [
                        'type'     => 'panel',
                        'name'     => 'Основные параметры',
                        'children' => ['name', 'contacts', 'comment'],
                    ],
                ],
            ],
        ];
    }

    public function getDriversAction()
    {
        $props = $this->params()->fromPost();

        return new JsonModel(['items' => $this->getService()->getDrivers($props)]);
    }
}