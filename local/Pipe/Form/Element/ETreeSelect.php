<?php
namespace Pipe\Form\Element;

use Zend\Form\Element;

class ETreeSelect extends Element
{
    protected $attributes = array(
        'type' => 'select',
    );
	
	public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }
}
