<?php
namespace Orders\Admin\Service;

use Pipe\String\Date;
use Zend\Db\Sql\Select;

class SystemService extends \Pipe\Mvc\Service\Admin\SystemService
{
    /**
     * @param Select $select
     * @return Select
     */
    protected function searchSelect($select)
    {
        $select
            ->columns(['id', 'name', 'date_from'])
            ->order('date_from DESC');

        return $select;
    }

    protected function getResultRow($module, $item)
    {
        return [
            'label'  =>  '(' . Date::toStr($item['date_from'], ['month' => 'short'], 'Y-m-d') . ') ' . $item['name'],
        ] + parent::getResultRow($module, $item);
    }
}