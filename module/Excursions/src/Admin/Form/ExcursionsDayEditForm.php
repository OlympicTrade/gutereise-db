<?php
namespace Excursions\Admin\Form;

use Pipe\Form\Form\Admin\Form;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class ExcursionsDayEditForm extends Form
{
    public function setOptions($options = [])
	{
	    $this->setPrefix('days[' . $options['model']->id() . ']');
        return parent::setOptions($options);
    }

    public function init()
    {
        $this->add([
            'name' => 'id',
            'type'  => ZElement\Hidden::class,
            'attributes'=>[
                'class' => 'day-id',
            ],
        ]);

        $this->add([
            'name' => 'transfer_time',
            'type'  => PElement\Time::class,
            'options' => [
                'label' => 'Время трансфера',
                'min' => '00:15',
            ],
        ]);

        $this->add([
            'name' => 'car_delivery_time',
            'type'  => PElement\Time::class,
            'options' => [
                'label' => 'Время подачи',
            ],
        ]);

        $this->add([
            'name' => 'min_time',
            'type'  => PElement\Time::class,
            'options' => [
                'label' => 'Мин. время начала',
            ],
        ]);

        $this->add([
            'name' => 'max_time',
            'type'  => PElement\Time::class,
            'options' => [
                'label' => 'Макс. время начала',
            ],
        ]);

        $this->add([
            'name' => 'transfer_id',
            'type'  => PElement\ESelect::class,
            'options' => [
                'label' => 'Трансфер',
                'model' => $this->options['model']->plugin('transfer'),
                'empty' => 'Не выбран',
                'sort'  => 'name',
            ],
        ]);

        $this->add([
            'name' => '[options][proposal][place_start]',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Адрес начала экскурсии'
            ],
            'attributes'=>[
                'placeholder' => '',
                'data-type' => 'address',
            ],
        ]);

        $this->add([
            'name' => '[options][proposal][place_end]',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Адрес окончания экскурсии'
            ],
            'attributes'=>[
                'placeholder' => '',
                'data-type' => 'address',
            ],
        ]);

        $this->add([
            'name' => '[options][proposal][desc]',
            'type'  => ZElement\Textarea::class,
            'options' => [
                'label' => 'Описание'
            ],
        ]);

        $this->add([
            'name' => '[options][proposal][price]',
            'type'  => ZElement\Textarea::class,
            'options' => [
                'label' => 'В стоимость влючено (доп. пункты)'
            ],
        ]);

        $this->add([
            'name' => '[options][proposal][price_guides]',
            'type'  => ZElement\Checkbox::class,
            'options' => [
                'label' => 'Гид',
                'checked_value'   => '1',
                'unchecked_value' => '0',
            ],
        ]);
        $this->get('[options][proposal][price_guides]')->setChecked(true);

        $this->add([
            'name' => '[options][proposal][price_museums]',
            'type'  => ZElement\Checkbox::class,
            'options' => [
                'label' => 'Музеи',
                'checked_value'   => '1',
                'unchecked_value' => '0',
            ],
        ]);
        $this->get('[options][proposal][price_museums]')->setChecked(true);

        $this->add([
            'name' => '[options][proposal][price_transport]',
            'type'  => ZElement\Checkbox::class,
            'options' => [
                'label' => 'Транспорт',
                'checked_value'   => '1',
                'unchecked_value' => '0',
            ],
        ]);
        $this->get('[options][proposal][price_transport]')->setChecked(true);

        $this->add([
            'name'  => 'timetable',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'foreigners'  => [
                        'name'  => 'Национальность',
                        'width' => '130',
                        'options' => [\Application\Admin\Model\Nationality::NATIONALITY_ALL => 'Все'] + \Application\Admin\Model\Nationality::$nationalityType,
                    ],
                    'tourists_from' => [
                        'name'    => 'Туристов от',
                        'width'   => '105',
                        'default' => '1',
                    ],
                    'tourists_to' => [
                        'name'    => 'до',
                        'width'   => '50',
                        'default' => '999',
                    ],
                    'duration' => [
                        'name'  => 'Длительность',
                        'width' => '120',
                        'type'  => 'time',
                        'options'   => [
                            'min' => '00:00',
                            'max' => '08:00',
                        ],
                    ],
                    'name' => [
                        'name'  => 'Название',
                        'width' => '400',
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('timetable'),
                'form'    => 'timetable',
            ],
        ]);

        $this->add([
            'name'  => 'guides',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'foreigners'  => [
                        'name'  => 'Национальность',
                        'width' => '130',
                        'options' =>
                            [\Application\Admin\Model\Nationality::NATIONALITY_ALL => 'Все'] +
                             \Application\Admin\Model\Nationality::$nationalityType,
                    ],
                    'tourists' => [
                        'name'  => 'Туристов от',
                        'width' => '105',
                        'default' => '1',
                    ],
                    'guides' => [
                        'name'  => 'Кол-во гидов',
                        'width' => '150',
                        'default' => '1',
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('guides'),
                'form'    => 'guides',
            ],
        ]);

        $this->add([
            'name'  => 'museums',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'foreigners' => [
                        'name'  => 'Национальность',
                        'width' => '130',
                        'options' => [\Application\Admin\Model\Nationality::NATIONALITY_ALL => 'Все'] + \Application\Admin\Model\Nationality::$nationalityType
                    ],
                    'museum_id' => [
                        'name'  => 'Музей',
                        'width' => '300',
                        'placeholder' => 'Выберите музей',
                        'module'  => 'museums',
                        'options' => \Museums\Admin\Model\Museum::getEntityCollection()
                    ],
                    'duration'  => [
                        'name'  => 'Длительность',
                        'width' => '120',
                        'type'  => 'time',
                        'options'   => [
                            'min' => '00:15',
                            'max' => '08:00',
                        ],
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('museums'),
                'form'    => 'museums',
            ],
        ]);

        $this->add([
            'name'  => 'transport',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'type' => [
                        'name'  => 'Тип',
                        'width' => '155',
                        'class' => 'transport-type',
                        'placeholder' => 'Тип транспорта',
                        'options' => [
                            1 => 'Автотранспорт',
                            2 => 'Водный транспорт',
                        ]
                    ],
                    'transport_id' => [
                        'name'  => 'Транспорт',
                        'width' => '300',
                        'placeholder' => 'Авторасчет',
                        'class' => 'transport-id',
                        'options' => \Transports\Admin\Model\Transport::getEntityCollection()
                    ],
                    'tourists_from' => [
                        'name'    => 'Туристов от',
                        'width'   => '105',
                        'default' => '1',
                    ],
                    'tourists_to' => [
                        'name'    => 'до',
                        'width'   => '50',
                        'default' => '999',
                    ],
                    'duration'  => [
                        'name'  => 'Длительность',
                        'width' => '120',
                        'type'  => 'time',
                        'options'   => [
                            'min' => '00:00',
                            'max' => '12:00',
                        ],
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('transport'),
                'form'    => 'transport',
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
                    'price_type' => [
                        'name'  => 'Рассчет',
                        'width' => '105',
                        'options' => \Excursions\Admin\Model\ExcursionExtra::$priceType
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
                'form'    => 'extra',
            ],
        ]);
    }
}