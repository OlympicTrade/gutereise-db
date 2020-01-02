<?php
namespace Guides\Admin\Controller;

use Orders\Admin\Service\OrdersService;
use Pipe\Calendar\Calendar;
use Pipe\DateTime\Date;
use Pipe\Mvc\Controller\AbstractActionController;
use Guides\Admin\Form\ProfileEditForm;
use Guides\Admin\Model\Guide;
use Guides\Admin\Model\GuideCalendar;
use Users\Model\User;
use Zend\View\Model\JsonModel;

class CalendarController extends AbstractActionController
{
    public function indexAction()
    {
        $view = $this->initLayout();
        $guide = $this->getGuide();

        $this->layout()->getVariable('meta')->title = 'Календарь (' . $guide->get('name') . ')';

        $view->setVariables([
            'guide'   => $guide,
            'guideId' => $this->params()->fromQuery('id') ? $guide->id() : 0,
        ]);

        return $view;
    }

    public function getCalendarPageAction()
    {
        $data = $this->params()->fromPost();

        $dt = Date::getDT($data['date']);

        switch ($data['shift']) {
            case 'prev':
                $dt->modify('-1 month');
                break;
            case 'next':
                $dt->modify('+1 month');
                break;
            default:
        }

        $calendar = new Calendar('guide-profile', $dt);
        $calendar->save();

        return new JsonModel([
            'html'  => $this->getViewHelper('guidesCalendarPage')(
                $calendar,
                $this->getGuide(),
                $this->getServiceManager()->get(OrdersService::class)
            ),
            'date'  => $dt->format(),
            'year'  => $dt->year(),
            'month' => $dt->month(),
        ]);
    }

    public function setDayBusynessAction()
    {
        $params = $this->params()->fromPost();

        $day = new GuideCalendar();
        $dt = Date::getDT($params['date']);

        $day->select()->where([
            'depend' => $this->getGuide()->id(),
            'date'   => $dt->format(),
        ]);

        if(!$day->load()) {
            $day->setVariables([
                'depend'   => $this->getGuide()->id(),
                'date'     => $dt->format(),
            ]);
        }

        $day->set('busy', $params['status']);

        $day->save();

        return new JsonModel([]);
    }

    public function setDefaultStatusAction()
    {
        $status = $this->params()->fromPost('status');
        $guide = $this->getGuide();

        $guide->get('options')->calendar->status = $status;
        $guide->save();

        return new JsonModel([]);
    }

    /**
     * @return \Guides\Admin\Model\Guide
     * @throws \Exception
     */
    protected function getGuide()
    {
        $guide = new Guide();

        $user = User::getInstance();

        $params = $this->params();

        $id = $params->fromQuery('id', null) ?? $params->fromRoute('id');

        if($id) {
            $user->checkRights('guides-guides');
            $guide->id($id);
        } else {
            $guide->select()
                ->where(['user_id' => User::getInstance()->id()]);
        }

        if(!$guide->load()) {
            throw new \Exception('Гид не найден');
        }

        return $guide->load();
    }

    /**
     * @return /Guides/Service/GuidesService
     */
    protected function getGuideService()
    {
        return $this->getServiceManager()->get('Guides/Service/GuidesService');
    }

    protected function getEditForm()
    {
        return new ProfileEditForm();
    }

    protected function getModel()
    {
        return new Guide();
    }
}