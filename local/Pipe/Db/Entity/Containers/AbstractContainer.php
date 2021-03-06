<?php
namespace Pipe\Db\Entity\Containers;

class AbstractContainer
{
    /** @var string */
    protected $source = '';

    /**
     * @var bool
     */
    protected $isChanged = false;

    public function set($value)
    {
        $this->setSource($value);
        return $this;
    }

    /**
     * @param mixed $options
     * @return string
     */
    public function get($options = null)
    {
        return $this->source;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->setSource('');
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setSource($value)
    {
        if($value == $this->source) {
            return $this;
        }

        $this->isChanged = true;
        $this->source = $value;

        /*if($_SERVER['REQUEST_URI'] == '/orders/calc-proposal/') {
            d($value);
        }*/

        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param null $changed
     * @return $this|bool
     */
    public function isChanged($changed = null)
    {
        if($changed === null) return $this->isChanged;

        $this->isChanged = $changed;

        return $this;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return $this->source;
    }

    public function serializeArray()
    {
        return $this->get();
    }
}