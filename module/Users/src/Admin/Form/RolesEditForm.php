<?php
namespace Users\Admin\Form;

use Pipe\Form\Filter\FArray;
use Pipe\Form\Form\Admin\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class RolesEditForm extends Form
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
            'options' => [
                'label' => 'Имя'
            ]
        ]);

        $this->add([
            'name'  => 'rights',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'resource' => [
                        'name'  => 'Ресурс',
                        'width' => '300',
                    ],
                    'access' => [
                        'name'  => 'Доступ',
                        'width' => '120',
                        'options' => [1 => 'Включен', 0 => 'Отключен'],
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('rights'),
                'form'    => 'rights',
            ],
        ]);

        /*$this->add([
            'name'  => 'rules',
            'type'  => 'Pipe\Form\Element\ECheckbox',
            'options' => [
                'label'      => 'Экскурсии по музеям',
                'model'      => $this->getModel()->plugin('rules'),
                'collection' => Module::getEntityCollection(),
            ],
        ]);*/
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput([
            'name'     => 'rights',
            'filters'  => [new FArray()],
        ]));

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