<?php
namespace Pipe\View\Helper;

use Zend\View\Helper\AbstractHelper;

class BtnSwitcher extends AbstractHelper
{
    public function __invoke($btns, $options = array())
    {
        $options = array_merge(array(
            'class'     => '',
            'name'      => '',
            'default'   => '',
        ), $options);

        $html = '<div class="btn-switcher ' . $options['class'] . '">';

        $value = isset($_GET[$options['name']]) ? $_GET[$options['name']] : $options['default'];

        foreach($btns as $btn) {
            $class = 'btn btn-gray';

            if($value == $btn['value']) {
                $class .= ' active';
            }

            $html .= '<a class="' . $class . '" href="' . $btn['url'] . '">' . $btn['name'] . '</a>';
        }

        $html .= '</div>';

        return $html;
    }
}