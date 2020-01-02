<?php
namespace Users\Admin\Form;

use Pipe\Form\Form\Admin\Form;

use Zend\Db\TableGateway\Feature\GlobalAdapterFeature as StaticDbAdapter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class UsersEditForm extends Form
{
    public function setData($data)
    {
        unset($data['password']);

        parent::setData($data);
    }

    public function init()
    {
        $this->addCommonElements(['id', 'fio']);

        $this->add([
            'name' => 'email',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'E-mail'
            ]
        ]);

        $this->add([
            'name' => 'password',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Пароль'
            ]
        ]);

        $this->add([
            'name' => 'role_id',
            'type'  => PElement\ESelect::class,
            'options' => [
                'label' => 'Тип',
                'model' => $this->getModel()->plugin('role'),
                'empty' => 'Не выбран',
                'sort'  => 'name',
            ]
        ]);

        /*$this->add([
            'name'  => 'rules',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields' => $fields = [
                    'module_id' => [
                        'name'    => 'Модуль',
                        'width'   => '250',
                        'options' => Module::getEntityCollection(),
                        'placeholder' => 'Не выбран',
                    ],
                    'access' => [
                        'name'  => 'Доступ',
                        'width' => '120',
                        'options' => [1 => 'Включен', 0 => 'Отключен'],
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('rules'),
                'sort'   => false,
            ],
        ]);*/
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $dbAdapter = StaticDbAdapter::getStaticAdapter();

        /*$inputFilter->add($factory->createInput([
            'name'     => 'rules',
            'filters'  => [new Pipe()],
        ]));*/

        $inputFilter->add($factory->createInput([
            'name'     => 'name',
            'required' => true,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
        ]));

        $inputFilter->add($factory->createInput([
            'name'     => 'email',
            'required' => true,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                ['name'    => 'EmailAddress'],
                [
                    'name'    => \Zend\Validator\Db\NoRecordExists::class,
                    'options' => [
                        'table'     => 'users',
                        'field'     => 'email',
                        'adapter'   => $dbAdapter,
                        'exclude' => [
                            'field' => 'email',
                            'value' => $this->options['model']->get('email')
                        ]
                    ],
                ],
            ],
        ]));

        $inputFilter->add($factory->createInput([
            'name'     => 'password',
            'required' => false,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'StringLength',
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min'      => 6,
                        'max'      => 30,
                    ],
                ],
                [
                    'name'    => 'Regex',
                    'options' => [
                        'pattern' => '/^[a-zA-Z1-9]*$/'
                    ],
                ],
            ],
        ]));

        /*$inputFilter->add($factory->createInput([
            'name'     => 'password_repeat',
            'required' => true,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'StringLength',
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min'      => 6,
                        'max'      => 30,
                    ],
                ],
                [
                    'name' => 'Identical',
                    'options' => [
                        'token' => 'password',
                    ],
                ],
            ],
        ]));*/

        $this->setInputFilter($inputFilter);
    }
}