<?php
namespace Pipe\Form\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

class EArray extends AbstractHelper
{
    public function __invoke(\Pipe\Form\Element\EArray $element)
    {
        return (new FormFactory())
            ->setForm($element->getForm())
            ->setPrefix($element->getName())
            ->setView($this->getView())
            ->structure($element->getOption('view'));
    }
}