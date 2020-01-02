<?php
namespace Application\Admin\Form;

use Pipe\Form\Form\Admin\Form;
use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class MenuEditForm extends Form
{
    public function init()
    {
        $this->addCommonElements(['id', 'name', 'parent']);

        $this->add([
            'name' => 'icon',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Иконка',
            ]
        ]);

        $this->add([
            'name' => 'sort',
            'type'  => ZElement\Number::class,
            'options' => [
                'label' => 'Сортировка',
            ]
        ]);

        $this->add([
            'name' => 'url[module]',
            'type'  => ZElement\Text::class,
            'options' => ['label' => 'Module']
        ]);

        $this->add([
            'name' => 'url[section]',
            'type'  => ZElement\Text::class,
            'options' => ['label' => 'Section']
        ]);

        $this->add([
            'name' => 'url[action]',
            'type'  => ZElement\Text::class,
            'options' => ['label' => 'Action']
        ]);

        $this->add([
            'name' => 'access[allow]',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Разрешен',
            ]
        ]);

        $this->add([
            'name' => 'access[deny]',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Запрещен',
            ]
        ]);

        $this->add([
            'name' => 'options[class]',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Class',
            ]
        ]);
    }
}