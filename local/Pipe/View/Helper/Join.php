<?php
namespace Pipe\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Join extends AbstractHelper
{
    public function __invoke($strings, $separator = ', ')
    {
        $joinStr = '';

        foreach($strings as $str) {
            $joinStr .= $str . $separator;
        }

        return trim($joinStr, $separator);
    }
}