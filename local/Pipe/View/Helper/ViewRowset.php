<?php
namespace Pipe\View\Helper;

use Zend\Escaper\Escaper;
use Zend\View\Helper\AbstractHelper;

class ViewRowset extends AbstractHelper
{
    public function __invoke($rows)
    {
        $html = '';

        foreach ($rows as $key => $val) {
            $val = trim($val, ', ');

            if(!$val) {
                continue;
            }

            $html .=
                '<div class="row">'
                    .'<div class="label">' . $key . ':</div>'
                    . $val
                .'</div>';
        }

        return $html;
    }
}