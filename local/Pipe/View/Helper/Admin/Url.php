<?php
namespace Pipe\View\Helper\Admin;

use Zend\View\Helper\AbstractHelper;

class Url extends AbstractHelper
{
    public function __invoke($module, $section, $action = '', $id = null)
    {
        return ADMIN_PREFIX . '/' . $this->getView()->url($module, $section, $action, $id);
    }
}