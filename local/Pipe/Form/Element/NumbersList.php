<?php

namespace Pipe\Form\Element;

use Zend\Form\Element\Select;

class NumbersList extends Select
{
    protected $attributes = [
        'type' => 'select',
    ];

    public function setOptions($options = [])
    {
        $this->setAttribute('data-type', 'numbers');

        $options = $options + [
            'options'   => [],
            'empty'     => null,
            'min'       => '1',
            'max'       => '15',
            'interval'  => '1',
        ];

        if($options['empty'] !== null) {
            $options['options'] += [''  => $options['empty']];
        }

        for($i = $options['min']; $i <= $options['max']; $i += $options['interval']) {
            $valOptions[$i] = $i;
        }

        $options['options'] += $valOptions;

        return parent::setOptions($options);
    }
}