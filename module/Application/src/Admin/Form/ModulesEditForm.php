<?php
namespace Application\Admin\Form;

use Pipe\Form\Form\Admin\Form;
use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class ModulesEditForm extends Form
{
    public function init()
    {
        $this->add([
            'name'    => 'module',
            'type'    => ZElement\Text::class,
            'options' => ['label' => 'Модуль']
        ]);

        $this->add([
            'name'    => 'section',
            'type'    => ZElement\Text::class,
            'options' => ['label' => 'Секция']
        ]);

        $this->addCommonElements(['id', 'name', 'parent', 'sort']);

        $this->add([
            'name' => 'options[icon]',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Иконка в меню',
            ]
        ])->setValue($this->getModel()->get('options')->icon);
    }
}