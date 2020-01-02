<?php

namespace Pipe\Form\Element;

use Zend\Form\Element\Text;

class Date extends Text
{
    protected $format = 'd.m.Y';

    public function setValue($value)
    {
        if($value instanceof \Pipe\DateTime\Date || $value instanceof \DateTime) {
            $value = $value->format($this->format);
        }

        return parent::setValue($value);
    }

    public function setOptions($options = [])
    {
        $this->setAttribute('data-type', 'date');

        if($options['format']) {
            $this->format = $options['format'];
        }

        return parent::setOptions($options);
    }
}