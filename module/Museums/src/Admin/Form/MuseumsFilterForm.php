<?php
namespace Museums\Admin\Form;

use Pipe\Form\Form\Admin\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use Base\Model\Partners;

class MuseumsFilterForm extends Form
{
    public function __construct()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'get');
        $this->setAttribute('autocomplete', 'off');

        $this->add(array(
            'name' => 'search',
            'type'  => ZElement\Text::class,
            'options' => array(),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'search',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}