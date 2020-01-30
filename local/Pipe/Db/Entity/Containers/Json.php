<?php
namespace Pipe\Db\Entity\Containers;

use \Zend\Json\Json as ZJson;
use Zend\Stdlib\ArrayObject;

class Json extends AbstractContainer
{
    /**
     * @var ArrayObject
     */
    protected $value;

    protected $touched = false;
    public function set($value) {
        $this->touched = true;
        $this->value = new ArrayObject($value, 2);

        return $this;
    }

    public function get($trace = null) {
        $this->touched = true;
        if($this->value === null) {
            $this->unserialize();
        }

        if($trace) {
            $trace = ltrim($trace, '[');
            $trace = str_replace(']', '', $trace);
            $trace = explode('[', $trace);

            $value = $this->value;
            for($i = 0; $i < count($trace); $i++) {
                $value = $value[$trace[$i]];
            }
        }

        return $this->value;
    }

    public function unserialize()
    {
        try {
            $this->value = new ArrayObject(ZJson::decode($this->source, ZJson::TYPE_ARRAY),2);
        } catch (\Exception $e) {
            $this->value = new ArrayObject([],2);
        }

        return $this->value;
    }

    public function serialize()
    {
        return ZJson::encode($this->value ? $this->value->getArrayCopy() : new \StdClass());
    }

    public function isChanged($changed = null)
    {
        if($changed === null && $this->touched) {
            $jsonStr = ZJson::encode($this->value ? $this->value->getArrayCopy() : new \StdClass());
            if($this->getSource() !== $jsonStr) {
                return true;
            }
            return false;
        }

        return parent::isChanged($changed);
    }

    public function setSource($value)
    {
        parent::setSource($value);
        $this->unserialize();

        return $this;
    }

    public function getSource()
    {
        return $this->source;
    }
}