<?php
namespace Pipe\Db\ResultSet;

use Zend\Db\ResultSet\AbstractResultSet;
use Pipe\Db\Entity\Entity;

class ResultSet extends AbstractResultSet
{
    /**
     * @var Entity
     */
    protected $prototype = null;

    /**
     * @return Entity
     */
    public function current()
    {
        $entity = $this->getPrototype()->getClearCopy();
        $entity->rFill(parent::current());

        return $entity;
    }

    public function setPrototype(Entity $prototype)
    {
        $this->prototype = $prototype;
    }

    public function getPrototype()
    {
        return $this->prototype;
    }
}