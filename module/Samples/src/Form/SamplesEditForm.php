<?php
namespace Samples\Form;

use Pipe\Form\Form\Admin\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class SamplesEditForm extends Form
{
    public function init()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add([
            'name' => 'id',
            'type'  => ZElement\Hidden::class,
        ]);

        $this->add([
            'name' => 'name',
            'type'  => ZElement\Text::class,
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput([
            'name'     => 'name',
            'required' => true,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
        ]));

        $this->setInputFilter($inputFilter);
    }
}