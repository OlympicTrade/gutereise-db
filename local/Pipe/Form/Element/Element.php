<?php

namespace Pipe\Form\Element;

use Pipe\Form\Form;
use Zend\Form\Element as ZElement;

class Element extends ZElement
{
    public function setOptions($options)
    {
        return parent::setOptions($options);
    }

    /** @var Form */
    protected $form;

    public function setForm($form) {
        $this->form = $form;
        return $this;
    }

    public function getForm() {
        return $this->form;
    }

    /** @var String */
    protected $prefix;

    public function setPrefix($prefix) {
        $this->prefix = $prefix;
        return $this;
    }

    public function getPrefix() {
        return $this->prefix;
    }

    public function getName($prefix = true)
    {
        $name = parent::getName();

        if($prefix) {
            return $name;
        }

        return substr($name, strlen($this->getPrefix()));
    }
}