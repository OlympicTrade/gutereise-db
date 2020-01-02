<?php
namespace Pipe\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Url extends AbstractHelper
{
    public function __invoke($module, $section, $action = '', $id = null)
    {
        $url = $module;

        if($section && $module != $section) {
            $url .= '-' . $section;
        }

        $url .= '/';

        if($action) {
            $url .= $action . '/';
        }

        if($id) {
            $url .= $id . '/';
        }

        return strtolower($url);
    }
}