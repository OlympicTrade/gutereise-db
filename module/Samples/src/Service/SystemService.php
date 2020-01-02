<?php
namespace Samples\Service;

use Pipe\Mvc\Service\Admin\TableService;

class SystemService extends TableService
{
    protected $table = '';

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
    }
}