<?php

namespace Pipe\Form\Element;

use Zend\Form\Element\Select;

class Time extends Select
{
    protected $attributes = [
        'type' => 'select',
    ];

    public function setValue($value)
    {
        if($value instanceof \Pipe\DateTime\Time || $value instanceof \DateTime) {
            $value = $value->format('H:i:s');
        }

        return parent::setValue($value);
    }

    public function setOptions($options = [])
    {
        $this->setAttribute('data-type', 'time');

        $options = $options + [
            'options'   => [],
            'empty'     => null,
            'min'       => '00:00',
            'max'       => '24:00',
            'interval'  => '00:15',
        ];

        if($options['empty'] !== null) {
            $options['options'] = [''  => $options['empty']] + $options['options'];
        }

        $min = new \DateTime('0000-00-00 ' . $options['min'] . ':00');
        $max = (new \DateTime('0000-00-00 ' . $options['max'] . ':00'))->modify('+1 minute');

        list($iH, $iM) = sscanf($options['interval'], '%d:%d');
        $interval = new \DateInterval('PT' . $iH . 'H' . $iM . 'M');
        $period = new \DatePeriod($min, $interval, $max);

        foreach ($period as $dt) {
            $valOptions[$dt->format('H:i:s')] = $dt->format('H:i');
        }

        $options['options'] += $valOptions;

        return parent::setOptions($options);
    }
}