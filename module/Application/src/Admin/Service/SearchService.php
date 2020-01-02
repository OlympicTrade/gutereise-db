<?php

namespace Application\Admin\Service;

use Application\Admin\Model\Module;
use Pipe\Mvc\Service\AbstractService;
use Pipe\Mvc\Service\Admin\SystemService;
use Users\Admin\Model\User;

class SearchService extends AbstractService
{
    public function autocomplete($query)
    {
        $modules = Module::getEntityCollection();
        $modules->select()
            ->order('sort ASC')
            ->where(['search' => 1]);

        $result = [];
        foreach($modules as $module) {
            $result = array_merge($result, $this->getSystemService($module)->getAutoComplete($query, $module));
        }

        return $result;
    }

    /*public function searchSql($query)
    {
        $modules = Module::getEntityCollection();
        $modules->select()
            ->columns(['id', 'module', 'section'])
            ->where(['search' => 1]);

        $sql = '(';
        $first = true;
        $user = User::getInstance();
        foreach($modules as $module) {
            if(!$user->checkRights($module->get('module') . '-' . $module->get('section'), false)) {
                continue;
            }

            $sql .= ($first ? '' : ') UNION (') . $this->getSystemService($module)->searchSql($query);
            $first = false;
        }
        $sql .= ')';

        return $sql;
    }*/

    public function searchData($query)
    {
        $data = array();

        $modules = Module::getEntityCollection();
        $modules->select()
            ->columns(array('id', 'name', 'module', 'section'))
            ->where(array('search' => 1));

        foreach($modules as $module) {
            $sql = $this->getSystemService($module)->searchSql($query);
            $result = self::getAdapter()->query($sql)->execute();

            if(!count($result)) {
                continue;
            }

            $data[] = array(
                'info'  => array(
                    'module'    => $module->get('module'),
                    'section'   => $module->get('section'),
                    'label'     => $module->get('name'),
                ),
                'data'  => $result
            );
        }

        return $data;
    }

    /**
     * @param $module
     * @return array|object
     */
    protected function getSystemService($module)
    {
        $serviceClassName = ucfirst($module->get('module')) . '\Admin\Service\SystemService';

        if(class_exists($serviceClassName)) {
            $service = $this->getServiceManager()->get($serviceClassName);
        } else {
            $service = $this->getServiceManager()->get(SystemService::class);
        }

        $service->setTable($module->get('module'));

        return $service;
    }
}