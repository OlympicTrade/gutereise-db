<?php
namespace Excursions\Admin\Controller;

use Guides\Admin\Model\Guide;
use Pipe\Db\Entity\EntityCollection;
use Pipe\Mvc\Controller\Admin\TableController;
use Excursions\Admin\Model\Excursion;
use Zend\View\Model\JsonModel;

class ExcursionsController extends TableController
{
    public function addDayAction()
    {
        $excursionId = $this->params()->fromPost('excursionId');
        $day = $this->getService()->addExcursionDay($excursionId);
        return new JsonModel(['html' => $this->viewHelper('ExcursionDayForm', $day)]);
    }

    public function delDayAction()
    {
        $dayId = $this->params()->fromPost('dayId');
        $this->getService()->delExcursionDay($dayId);
        return new JsonModel(['status' => 1]);
    }
}