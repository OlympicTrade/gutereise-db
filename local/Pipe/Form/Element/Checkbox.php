<?php

namespace Pipe\Form\Element;

use Zend\Form\Element as ZElement;

class Checkbox extends ZElement\Checkbox
{
    public function setOptions($options = [])
    {
        $options = $options + [
            'checked_value'   => '1',
            'unchecked_value' => '0',
        ];

        return parent::setOptions($options);
    }
}