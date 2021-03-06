<?php
namespace Pipe\Db\Entity;

class EntityHierarchy extends Entity
{
    /**
     * @var string
     */
    protected $parentField = 'parent';

    /**
     * @var int
     */
    protected $parentId = null;

    public function clear()
    {
        parent::clear();

        $this->parentId = null;

        return $this;
    }

    public function fill($data)
    {
        parent::fill($data);

        $this->setParentId($this->get($this->parentField));

        return $this;
    }


    public function remove($transaction = true)
    {
        if(!$this->id) {
            return false;
        }

        if($transaction) {
            $this->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
        }

        $children = $this->getChildren();

        foreach($children as $child) {
            $commit = $child->remove(false);

            if(!$commit) {
                if($transaction) {
                    $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                    return false;
                }
            }
        }

        $commit = parent::remove(false);

        if ($transaction) {
            if ($commit) {
                $this->getDbAdapter()->getDriver()->getConnection()->commit();
            } else {
                $this->getDbAdapter()->getDriver()->getConnection()->rollback();
            }
        }

        return $commit;
    }

    /**
     * @return EntityCollectionHierarchy
     */
    public function getChildren()
    {
        $entityCollection = $this->getCollection();
        $entityCollection->setParentId($this->id());

        return $entityCollection;
    }

    /**x
     * @return EntityCollection
     */
    public function getCollection()
    {
        $collection = new EntityCollectionHierarchy();
        $prototype  = $this->getClearCopy();

        /*if($children) {
            $prototype->setParentId($this->id());
        }*/

        $collection->setPrototype($prototype);
        $collection->setParentId($this->id());

        return $collection;
    }

    /**
     * @return $this|null
     */
    public function getParent()
    {
        if($this->getParentId() == 0) {
            return null;
        }

        $entity = clone $this;
        $entity->clearSelect();

        $entity->id($this->getParentId());

        return $entity;
    }

    /**
     * @param int $parentId
     * @return $this
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }
}