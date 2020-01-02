<?php
namespace Pipe\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Date extends AbstractHelper
{
    public function __invoke($date, $options = [], $pattern = 'Y-m-d H:i:s')
    {
        $options = array_merge(array(
            'day'    => true,
            'month'  => true,
            'year'   => true,
            'time'   => false,
        ), $options);

        if($date instanceof \DateTime) {
            $dt = $date;
        } else {
            $dt = \DateTime::createFromFormat($pattern, $date);
        }

        $str = '';
        if($options['day']) {
            $str .= $dt->format('d');
        }

        if($options['month']) {
            if($options['day']) {
                $str .= ' ' .  \Pipe\String\Date::$months2[$dt->format('n')];
            } else {
                $str .= ' ' .  \Pipe\String\Date::$months[$dt->format('n')];
            }
        }

        if($options['year']) {
            $str .= ' ' . $dt->format('Y');
        }

        if($options['time']) {
            $str .= ' ' . $dt->format('H') . ':' . $dt->format('i');
        }

        return $str;
    }
}