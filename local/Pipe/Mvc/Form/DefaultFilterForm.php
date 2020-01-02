<?php

namespace Pipe\Mvc\Form;

use Pipe\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class DefaultFilterForm extends Form
{
    public function __construct()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'get');
        $this->setAttribute('autocomplete', 'off');

        $this->add([
            'name' => 'search',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [],
        ]);
    }

        public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'search',
            'required' => false,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
        )));

        $this->setInputFilter($inputFilter);
    }
}