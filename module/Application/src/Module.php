<?php
namespace Application;

use Pipe\Db\Entity\ConfigCollector;
use Zend\Mvc\MvcEvent;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature as StaticDbAdapter;
use Pipe\Cache\CacheFactory as StaticCacheAdapter;
use Zend\Mvc\Controller\AbstractActionController;

use Pipe\Compressor\Compressor;
use Pipe\Form\Element as PElement;
use Pipe\Form\View\Helper as PHelper;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getApplication();
        $sm  = $app->getServiceManager();
        $em  = $app->getEventManager();
        $sEm = $em->getSharedManager();

        StaticDbAdapter::setStaticAdapter($sm->get('Zend\Db\Adapter\Adapter'));
        $sm->get('Zend\Db\Adapter\Adapter')->getProfiler()->getQueryProfiles();

        $sEm->attach(AbstractActionController::class,MvcEvent::EVENT_DISPATCH,
            function(MvcEvent $e) {
                $sm = $e->getApplication()->getServiceManager();
                StaticCacheAdapter::setAdapter($sm->get('HtmlCache'), 'html');
            }, 200);

        $sEm->attach(AbstractActionController::class, MvcEvent::EVENT_DISPATCH,
            [$this, 'preDispatch'], 50);

        $sEm->attach(AbstractActionController::class, MvcEvent::EVENT_DISPATCH, function() use ($sm) {
            $sm->get('Zend\Db\Adapter\Adapter')->getProfiler()->getQueryProfiles();
            //dd($sm->get('Zend\Db\Adapter\Adapter')->getProfiler()->getQueryProfiles());
        });

        $sm->get('ViewHelperManager')->get('FormElement')
            ->addClass(PElement\EArray::class, PHelper\EArray::class)
            ->addClass(PElement\ECollection::class, PHelper\ECollection::class)
            ->addClass(PElement\ECheckbox::class, PHelper\ECheckbox::class)
            ->addClass(PElement\ESelect::class, PHelper\ESelect::class)
            //->addClass(PElement\Checkbox::class, PHelper\ESelect::class)
        ;
    }

    public function compressCssJs()
    {
        $compressor = Compressor::getInstance();

        //$compressor->addModule('contacts');

        $compressor->addFiles([
            PUBLIC_DIR . '/fonts/fontawesome/font.css',
            PUBLIC_DIR . '/fonts/proxima-nova/font.css',
            PUBLIC_DIR . '/css/libs/reset.scss',
            PUBLIC_DIR . '/css/libs/grid.scss',
            PUBLIC_DIR . '/css/libs/fancybox.css',
            PUBLIC_DIR . '/css/libs/calendar.scss',
            PUBLIC_DIR . '/css/elements.scss',
            PUBLIC_DIR . '/css/main.scss',

            PUBLIC_DIR . '/css/modules/translator.scss',
            PUBLIC_DIR . '/css/modules/documents.scss',
            PUBLIC_DIR . '/css/modules/balance.scss',
            PUBLIC_DIR . '/css/modules/guides.scss',
            PUBLIC_DIR . '/css/modules/orders.scss',
        ], 'css', 'desktop');

        $compressor->addFiles([
            PUBLIC_DIR . '/js/libs/cookie.js',
            PUBLIC_DIR . '/js/libs/inputmask.js',
            PUBLIC_DIR . '/js/libs/fancybox/fancybox.js',
            PUBLIC_DIR . '/js/libs/pipe/messages.js',
            PUBLIC_DIR . '/js/libs/pipe/common.js',
            PUBLIC_DIR . '/js/libs/pipe/calc.js',
            PUBLIC_DIR . '/js/libs/pipe/template.js',
            PUBLIC_DIR . '/js/libs/pipe/db-table-list.js',
            PUBLIC_DIR . '/js/libs/pipe/db-table-edit.js',
            PUBLIC_DIR . '/js/libs/pipe/calendar.js',
            PUBLIC_DIR . '/js/libs/pipe/search.js',
            PUBLIC_DIR . '/js/calc.js',
            PUBLIC_DIR . '/js/main.js',
        ], 'js', 'desktop');
    }

    public function preDispatch(MvcEvent $e)
    {
        //$moduleMdl = $e->getApplication()->getServiceManager()->get('Module');
        $moduleMdl = \Application\Common\Model\Module::getInstance();

        if(!$moduleMdl->module()) {
            $route = $e->getRouteMatch();

            $module = explode('-', $route->getParam('module'));
            $moduleMdl
                ->setModule($module[0])
                ->setSection($module[1])
                ->setModel($route->getParam('model'));

            $this->compressCssJs();
        }
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}