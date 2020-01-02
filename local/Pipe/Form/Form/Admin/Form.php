<?php
namespace Pipe\Form\Form\Admin;

use Pipe\Form\Form\Form as CommonForm;
use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class Form extends CommonForm {

    /*
     * ['id', 'fio', 'name', 'parent', 'contacts', 'comment', 'text', 'sort', 'company_details']
     */
    protected function addCommonElements($options)
    {
        if(in_array('id', $options)) {
            $this->add([
                'name' => 'id',
                'type'  => ZElement\Hidden::class,
            ]);
        }

        if(in_array('fio', $options)) {
            $this->add([
                'name' => 'name',
                'options' => [
                    'label' => 'ФИО',
                ]
            ]);
        }

        if(in_array('name', $options)) {
            $this->add([
                'name' => 'name',
                'options' => [
                    'label' => 'Название',
                ]
            ]);
        }

        if(in_array('parent', $options)) {
            $this->add([
                'name' => 'parent',
                'type'  => PElement\ESelect::class,
                'options' => [
                    'label' => 'Привязан к',
                    'model' => clone $this->getModel(),
                    'empty' => 'Не выбран',
                    'sort'  => 'name',
                ]
            ]);
        }

        if(in_array('text', $options)) {
            $this->add([
                'name' => 'text',
                'type'  => ZElement\Textarea::class,
                'attributes'=> [
                    'class' => 'editor',
                    'id'    => 'page-text'
                ],
            ]);
        }

        if(in_array('sort', $options)) {
            $this->add([
                'name' => 'sort',
                'options' => [
                    'label' => 'Сортировка',
                ]
            ]);
        }

        if(in_array('comment', $options)) {
            $this->add([
                'name' => 'comment',
                'type'  => ZElement\Textarea::class,
                'options' => [
                    'label' => 'Комментарий',
                ]
            ]);
        }

        if(in_array('contacts', $options)) {
            $this->add([
                'name'  => 'contacts',
                'type'  => PElement\EArray::class,
                'options' => [
                    'elements' => [
                        'phones' => [
                            'phone1' => 'Телефон 1',
                            'phone2' => 'Телефон 2',
                        ],
                        'emails' => [
                            'email1' => 'E-mail 1',
                            'email2' => 'E-mail 2',
                        ],
                    ],
                    'view' => [
                        [
                            ['width' => 50, 'element' => '[phones][phone1]'],
                            ['width' => 50, 'element' => '[phones][phone2]'],
                        ],
                        [
                            ['width' => 50, 'element' => '[emails][email1]'],
                            ['width' => 50, 'element' => '[emails][email2]'],
                        ],
                    ]
                ],
            ]);
        }

        if(in_array('company_details', $options)) {
            $this->add([
                'name'  => 'company_details',
                'type'  => PElement\EArray::class,
                'options' => [
                    'elements' => [
                        'director' => [
                            'name'       => 'Имя',
                            'surname'    => 'Фамилия',
                            'patronymic' => 'Отчество',
                            'sex'        => [
                                'element' => new ZElement\Select('_', [
                                    'label'   => 'Пол',
                                    'options' => [
                                        ''    => 'Не указан',
                                        'm'   => 'Мужчина',
                                        'w'   => 'Женщина',
                                    ],
                                ]),
                            ],
                        ],
                        'company' => [
                            'name' => 'Название', 'inn' => 'ИНН', 'kpp' => 'КПП', 'ogrn' => 'ОГРН',
                            'org_form' => [
                                'element' => new ZElement\Select('_', [
                                    'label'   => 'Орг. прав. форма',
                                    'options' => [
                                        ''    => 'Не указана',
                                        'ИП'  => 'ИП',
                                        'ООО' => 'ООО',
                                        'ЗАО' => 'ЗАО',
                                        'ОАО' => 'ОАО',
                                    ]
                                ]),
                            ],
                        ],
                        'bank' => [
                            'name' => 'Банк', 'bik' => 'БИК', 'rs' => 'Р/С', 'ks' => 'К/С',
                        ],
                        'address' => [
                            'reg' => 'Юридический адрес',
                            'fact' => 'Фактический адрес',
                        ],
                        'ip' => [
                            'passport' => 'Паспорт №', 'passport_issued' => 'Паспорт выдан',
                            'passport_date' => 'Дата выдачи', 'birthday' => 'День рождения',
                        ]
                    ],
                    'view' => [
                        [
                            ['width' => 50, 'element' => '[company][org_form]'],
                            ['width' => 50, 'element' => '[company][name]'],
                        ],
                        [
                            ['width' => 50, 'element' => '[company][inn]'],
                            ['width' => 50, 'element' => '[company][ogrn]'],
                        ],
                        [['width' => 100, 'element' => '[bank][name]']],
                        [
                            ['width' => 33, 'element' => '[bank][bik]'],
                            ['width' => 33, 'element' => '[bank][rs]'],
                            ['width' => 33, 'element' => '[bank][ks]'],
                        ],
                        [
                            ['width' => 50, 'element' => '[address][reg]'],
                            ['width' => 50, 'element' => '[address][fact]'],
                        ],
                        ['type' => 'tag', 'text' => 'Директор'],
                        [
                            ['width' => 25, 'element' => '[director][surname]'],
                            ['width' => 25, 'element' => '[director][name]'],
                            ['width' => 25, 'element' => '[director][patronymic]'],
                            ['width' => 25, 'element' => '[director][sex]'],
                        ],
                        ['type' => 'tag', 'text' => 'Для Юр. лиц'],
                        [
                            ['width' => 50, 'element' => '[company][kpp]'],
                        ],
                        ['type' => 'tag', 'text' => 'Для ИП'],
                        [
                            ['width' => 25, 'element' => '[ip][passport]'],
                            ['width' => 25, 'element' => '[ip][passport_issued]'],
                            ['width' => 25, 'element' => '[ip][passport_date]'],
                            ['width' => 25, 'element' => '[ip][birthday]'],
                        ],
                    ]
                ],
            ]);
        }
    }
}