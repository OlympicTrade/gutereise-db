<?php
namespace Guides\Admin\Form;

use Application\Admin\Model\Settings;
use Pipe\Form\Filter\FArray;
use Pipe\Form\Form\Admin\Form;
use Museums\Admin\Model\Museum;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ProfileEditForm extends Form
{
    /*public function setOptions($options = [])
    {
        parent::setOptions($options);

        $this->get('user_id')->setOptions([
            'model' => $options['model']->plugin('user'),
            'empty' => 'Не выбран',
            'sort'  => 'name',
        ]);

        return $this;
    }*/

    public function init()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add([
            'name' => 'id',
            'type'  => ZElement\Hidden::class,
        ]);

        $this->addCommonElements(['name', 'contacts']);

        $this->add([
            'name'  => 'museums',
            'type'  => 'Pipe\Form\Element\ECheckbox',
            'options' => [
                'label'      => 'Экскурсии по музеям',
                'model'      => $this->getModel()->plugin('museums'),
                'collection' => Museum::getEntityCollection(),
            ],
        ]);

        $this->add([
            'name'  => 'languages',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'lang_id' => [
                        'name' => 'Язык',
                        'options' => Settings::getInstance()->plugin('languages'),
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('languages'),
            ],
        ]);

        return $this;
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

        $inputFilter->add($factory->createInput([
            'name'     => 'languages',
            'filters'  => [new FArray()],
        ]));

        $inputFilter->add($factory->createInput([
            'name'     => 'museums',
            'filters'  => [new FArray()],
        ]));

        $this->setInputFilter($inputFilter);
    }
}