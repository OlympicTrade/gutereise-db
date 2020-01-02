<?php
namespace Documents\Admin\Form;

use Pipe\Form\Form\Admin\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class DocumentsEditForm extends Form
{
    public function init()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->addCommonElements(['id', 'name']);

        $this->add([
            'name' => 'file',
            'type'  => 'Zend\Form\Element\File',
            'options' => [
                'label' => 'Добавить новый файл (.docx)',
                'file'  => $this->getModel()->getPublicFile(),
            ]
        ]);
    }

    public function setFilters()
    {
        parent::setFilters();

        $inputFilter = $this->getInputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput([
            'name'     => 'file',
            'required' => false,
            'validators' => [
                [
                    'name' => \Zend\Validator\File\Extension::class,
                    'options' => [
                        'extension' => ['docx'],
                    ]
                ],
                [
                    'name' => \Zend\Validator\File\Size::class,
                    'options' => [
                        'max' => '3MB',
                    ]
                ],
            ],
        ]));

        return $this;
    }
}