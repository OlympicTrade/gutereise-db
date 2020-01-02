<?php
namespace Pipe\View\Helper;

use Zend\View\Helper\AbstractHelper as ZendAbstractHelper;

class AbstractHelper extends ZendAbstractHelper
{
    protected function getModule()
    {
        return $this->getView()->module;
    }

    protected function getSection()
    {
        return $this->getView()->section;
    }
}