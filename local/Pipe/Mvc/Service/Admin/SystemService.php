<?php
namespace Pipe\Mvc\Service\Admin;

use Pipe\Mvc\Service\AbstractService;
use Pipe\String\Translit;
use Zend\Db\Sql\Where;

class SystemService extends AbstractService
{
    /*protected $table = '';

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function searchSql($query)
    {
        return 'SELECT id, name FROM ' . $this->table . ' WHERE name LIKE("%' . $query . '%")';
    }

    public function autocomplete($query)
    {
        $sql = $this->searchSql($query) . ' ORDER BY name ASC LIMIT 10';

        return self::getAdapter()->query($sql)->execute();
    }*/

    protected $table = '';

    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    public function searchSql($query)
    {
        return 'SELECT id, name FROM ' . $this->table . ' WHERE name LIKE("%' . $query . '%")';
    }

    public function autocomplete($query)
    {
        $sql = $this->searchSql($query) . ' ORDER BY name ASC LIMIT 10';

        return self::getAdapter()->query($sql)->execute();
    }

    /**
     * @param Select $select
     * @return Select
     */
    protected function searchSelect($select)
    {
        return $select;
    }

    protected function getResultRow($module, $item)
    {
        return [
            'type'   => 'item',
            'label'  => $item['name'],
            'module' => $module->get('module'),
            'url'    => '/' . $module->get('module') . '/edit/' . $item['id'] . '/',
        ];
    }

    public function getAutoComplete($query, $module)
    {
        $result = [];
        $maxRows = 4;

        $select = $this->getSql()->select();
        $select
            ->columns(['id', 'name'])
            ->from(['t' => $this->table]);

        $this->searchSelect($select);

        $where = new Where();
        foreach(Translit::searchVariants($query) as $queryVar) {
            $where->or->like('name', '%' . $queryVar . '%');
        }

        $select->where->addPredicate($where);

        $items = $this->execute($select);

        if(count($items)) {
            $result[] = [
                'type' => 'title',
                'label' => $module->get('name'),
            ];

            $i = 0;
            foreach ($items as $item) {
                $i++;
                if($i > 12) break;

                $result[] = [
                    'hide'   => ($i > $maxRows ? true : false)
                ] + $this->getResultRow($module, $item);
            }

            if($i > $maxRows) {
                $result[] = [
                    'label' => 'Показать все',
                    'type' => 'show-all',
                    'module' => $module->get('module'),
                ];
            }
        }

        return $result;
    }
}