<?php
namespace Excursions\Admin\Form;

use Pipe\Form\Form\Admin\Form;

use Guides\Admin\Model\Guide;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ExcursionsFilterForm extends Form
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

        $this->add(array(
            'name' => 'guide',
            'type'  => PElement\ESelect::class,
            'options' => array(
                'model' => new Guide(),
                'sort'  => 'name'
            )
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

        $inputFilter->add($factory->createInput(array(
            'name'     => 'guide',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}