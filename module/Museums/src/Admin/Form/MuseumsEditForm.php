<?php
namespace Museums\Admin\Form;

use Pipe\Form\Filter\FArray;
use Pipe\Form\Form\Admin\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class MuseumsEditForm extends Form
{
    public function init()
    {
        $this->addCommonElements(['id', 'name', 'contacts', 'comment']);

        $this->add([
            'name' => 'proposal_title',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Заголовок'
            ],
        ]);

        $this->add([
            'name' => 'proposal_title_plural',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Заголовок (родительский падеж)'
            ],
        ]);

        $this->add([
            'name' => 'proposal_place_start',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Адрес начала экскурсии'
            ],
        ]);

        $this->add([
            'name' => 'proposal_desc',
            'type'  => ZElement\Textarea::class,
            'options' => [
                'label' => 'Описание'
            ],
        ]);

        $this->add([
            'name' => 'proposal_price',
            'type'  => ZElement\Textarea::class,
            'options' => [
                'label' => 'В стоимость влючены: (каждый пункт на новой строке)'
            ],
        ]);

        $this->add([
            'name'  => 'tickets',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'foreigners' => [
                        'name'  => 'Национальность',
                        'width' => '135',
                        'options' => \Application\Admin\Model\Nationality::$nationalityType
                    ],
                    'adult_price' => [
                        'name'  => 'Взрослый',
                        'width' => '100',
                    ],
                    'child_price' => [
                        'name'  => 'Десткий',
                        'width' => '100',
                    ],
                    'min_price' => [
                        'name'  => 'Мин. цена',
                        'width' => '90',
                    ],
                    'date_from'  => [
                        'type'  => 'date',
                        'options' => ['format' => 'd.m'],
                        'name'  => 'Действует с',
                        'width' => '115',
                        'class' => 'datepicker-dm',
                    ],
                    'date_to'  => [
                        'type'  => 'date',
                        'options' => ['format' => 'd.m'],
                        'name'  => 'по (включительно)',
                        'width' => '160',
                        'class' => 'datepicker-dm',
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('tickets'),
            ],
        ]);

        $this->add([
            'name'  => 'guides',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'age' => [
                        'name'  => 'Возраст',
                        'width' => '130',
                        'options' => \Application\Admin\Model\Age::$ageType
                    ],
                    'foreigners' => [
                        'name'  => 'Национальность',
                        'width' => '130',
                        'options' => \Application\Admin\Model\Nationality::$nationalityType
                    ],
                    'count' => [
                        'name'  => 'Размер группы от',
                        'width' => '150',
                    ],
                    'price' => [
                        'name'  => 'Стоимость',
                        'width' => '110',
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('guides'),
            ],
        ]);

        $this->add([
            'name'  => 'extra',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'name' => [
                        'name'  => 'Название',
                        'width' => '120',
                    ],
                    'proposal_name' => [
                        'name'  => 'Название для КП',
                        'width' => '150',
                    ],
                    'foreigners' => [
                        'name'  => 'Национальность',
                        'width' => '130',
                        'options' => \Application\Admin\Model\Nationality::$nationalityType
                    ],
                    'transport_type' => [
                        'name'  => 'Транспорт',
                        'width' => '105',
                        'options' => \Museums\Admin\Model\MuseumExtra::$transportType
                    ],
                    'price_type' => [
                        'name'  => 'Рассчет',
                        'width' => '105',
                        'options' => \Museums\Admin\Model\MuseumExtra::$priceType
                    ],
                    'tourists_from' => [
                        'name'    => 'Туристов от',
                        'width'   => '100',
                        'default' => '1',
                    ],
                    'tourists_to' => [
                        'name'    => 'до',
                        'width'   => '50',
                        'default' => '999',
                    ],
                    'income' => [
                        'name'  => 'Доход',
                        'width' => '75',
                    ],
                    'outgo' => [
                        'name'  => 'Расход',
                        'width' => '75',
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('extra'),
            ],
        ]);

        $this->add([
            'name'  => 'weekends',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'date_from'  => [
                        'type'  => 'date',
                        'options' => ['format' => 'd.m'],
                        'name'  => 'Не работает с',
                        'width' => '145',
                        'class' => 'datepicker-dm',
                    ],
                    'date_to'  => [
                        'type'  => 'date',
                        'options' => ['format' => 'd.m'],
                        'name'  => 'по (включительно)',
                        'width' => '160',
                        'class' => 'datepicker-dm',
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('weekends'),
            ],
        ]);

        $this->add([
            'name'  => 'worktime',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'weekday' => [
                        'name'  => 'День недели',
                        'width' => '150',
                        'options' => \Pipe\String\Date::$weekdays,
                    ],
                    'time_from'  => [
                        'type'  => 'time',
                        'options' => ['interval' => '00:30'],
                        'name'  => 'с',
                        'width' => '90',
                    ],
                    'time_to'  => [
                        'type'  => 'time',
                        'options' => ['interval' => '00:30'],
                        'name'  => 'по',
                        'width' => '90',
                    ],
                    'foreigners' => [
                        'name'  => 'Национальность',
                        'width' => '130',
                        'options' => \Application\Admin\Model\Nationality::$nationalityType
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('worktime'),
            ],
        ]);
    }
}