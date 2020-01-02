<?php
namespace Pipe\Db\Plugin;

use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\EntityCollection;

class Collection extends EntityCollection implements PluginInterface
{
    protected $parentFiled = 'depend';

    public function getParentFiled()
    {
        return $this->parentFiled;
    }

    public function setParentFiled($parentFiled)
    {
        $this->parentFiled = $parentFiled;
        return $this;
    }

    public function setParentId($id)
    {
        $this->select()->where(array($this->parentFiled => $id));
        return $this;
    }
}