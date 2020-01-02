<?php
namespace Pipe\Db\Plugin;

use Pipe\Db\AbstractDb;
use Pipe\Db\Entity\Entity;

class PluginAbstract extends AbstractDb implements PluginInterface
{
    /**
     * @var Entity
     */
    protected $parent = null;

    /**
     * @var string
     */
    protected $parentFiled = 'depend';

    /**
     * @var bool
     */
    protected $loaded = false;

    /**
     * @var bool
     */
    protected $changed = false;

    /**
     * @return bool
     */
    public function load(){
        return true;
    }

    /**
     * @return bool
     */
    public function save(){
        return true;
    }

    /**
     * @return bool
     */
    public function remove(){
        return true;
    }

    /**
     * @param array $data
     * @return PluginAbstract
     */
    public function rFill($data)
    {
        return $this->fill($data);
    }

    /**
     * @param array $data
     * @return PluginAbstract
     */
    public function fill($data)
    {
        return $this;
    }

    /**
     * @param \Pipe\Db\Entity\Entity $parent
     * @return Comments
     */
    public function setParent(Entity $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return \Pipe\Db\Entity\Entity
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parent->id();
    }

    /**
     * @param $result
     * @param string $prefix
     * @return array
     */
    public function serializeArray($result = array(), $prefix = '')
    {
        return array();
    }

    public function __clone()
    {

    }
}