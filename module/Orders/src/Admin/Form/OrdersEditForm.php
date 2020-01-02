<?php
namespace Orders\Admin\Form;

use Application\Admin\Model\Currency;
use Orders\Admin\Model\OrderConstants;
use Zend\InputFilter\Factory as InputFactory;
use Pipe\Form\Form\Admin\Form;

use Clients\Admin\Model\Client;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;
use Pipe\Form\Filter  as PFilter;

class OrdersEditForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'id',
            'type'  => ZElement\Hidden::class,
        ]);

        $this->add([
            'name'     => 'options[color]',
            'type'     => ZElement\Select::class,
            'required' => false,
            'options'  => [
                'label'   => 'Цвет',
                'options' => OrderConstants::$colors,
            ],
        ]);

        $this->add([
            'name' => 'income',
            'type'  => ZElement\Hidden::class,
        ]);

        $this->add([
            'name' => 'outgo',
            'type'  => ZElement\Hidden::class,
        ]);

        $this->add([
            'name' => 'errors',
            'type'  => ZElement\Hidden::class,
        ]);

        $this->add([
            'name' => 'agency',
            'type' => ZElement\Select::class,
            'options' => [
                'options' => OrderConstants::$clientTypes,
                'label'   => 'Клиент',
            ]
        ]);

        /*$this->add([
            'name'  => 'country',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Страна',
            ]
        ]);*/

        $this->add([
            'name' => 'manager_id',
            'type'  => PElement\ESelect::class,
            'options' => [
                'label' => 'Менеджер',
                'model' => $this->getModel()->plugin('manager'),
                'empty' => 'Не выбран',
            ]
        ]);

        $this->add([
            'name' => 'status',
            'type' => ZElement\Select::class,
            'options' => [
                'options' => OrderConstants::$statuses,
                'label' => 'Статус',
            ]
        ]);

        $this->add([
            'name' => 'date_from',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Дата',
            ],
            'attributes' => [
                'class' => 'datepicker std-input',
            ],
        ]);

        /*$this->add([
            'name' => 'duration',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Длительность',
            ]
        ]);*/

        $this->add([
            'name' => 'comment',
            'type'  => ZElement\Textarea::class,
            'options' => [
                'label' => 'Комментарий',
            ]
        ]);

        /*$this->add([
            'name' => 'place_start',
            'type'  => ZElement\Textarea::class,
            'options' => [
                'label' => 'Адрес начала',
            ]
        ]);*/

        $this->add([
            'name' => 'adults',
            'type'  => ZElement\Number::class,
            'options' => [
                'label' => 'Кол-во взрослых',
            ],
        ]);

        $this->add([
            'name' => 'children',
            'type'  => ZElement\Number::class,
            'options' => [
                'label' => 'Кол-во детей',
            ],
        ]);

        $this->add([
            'name' => 'lang_id',
            'type' => PElement\ESelect::class,
            'options' => [
                'label' => 'Язык',
                'sort'    => 'id',
                'model' => $this->getModel()->plugin('language'),
                'empty' => 'Не выбран',
            ],
            'attributes' => [
                'value' => 'ru'
            ],
        ]);

        $this->add([
            'name' => 'options[currency][currency]',
            'type'  => ZElement\Select::class,
            'required'  => false,
            'options' => [
                'label'    => 'Валюта',
                'options'  => Currency::$currencyTypes,
            ],
        ]);

        $this->add([
            'name' => 'options[currency][rate]',
            'type'  => ZElement\Text::class,
            'options' => [
                'label'   => 'Курс',
            ],
            'attributes' => [
                'placeholder' => 'Авто',
                'class' => 'currency_rate std-input',
            ]
        ]);

        $this->add([
            'name' => 'name',
            'type'  => ZElement\Text::class,
            'required' => false,
            'options' => [
                'label' => 'Название'
            ],
        ]);

        /*$this->add([
            'name' => 'attrs[client_name]',
            'type'  => ZElement\Text::class,
            'attributes' => [
                'class' => 'std-input',
                'placeholder' => 'Имя',
            ]
        ]);

        $this->add([
            'name' => 'attrs[client_phone]',
            'type'  => ZElement\Text::class,
            'attributes' => [
                'class' => 'std-input',
                'placeholder' => 'Телефон',
            ]
        ]);

        $this->add([
            'name' => 'attrs[client_email]',
            'type'  => ZElement\Text::class,
            'attributes' => [
                'class' => 'std-input',
                'placeholder' => 'E-mail',
            ]
        ]);*/

        $this->add([
            'name' => 'proposal',
            'type'  => ZElement\Textarea::class,
            'attributes' => [
                'class' => 'text editor',
            ]
        ]);

        $this->add([
            'name' => 'options[proposal][autocalc]',
            'type'  => ZElement\Checkbox::class,
            'required'  => false,
            'options' => [
                'label' => 'Авторассчет ком. предложения',
                'checked_value'   => 1,
                'unchecked_value' => 0,
            ],
            'attributes'=> [
                'class' => 'autocalc autocalc-proposal',
            ],
        ]);

        $this->add([
            'name' => 'options[proposal][generalize]',
            'type'  => ZElement\Checkbox::class,
            'required'  => false,
            'options' => [
                'label' => 'Обобщить стоимость',
                'checked_value'   => 1,
                'unchecked_value' => 0,
            ],
        ]);

        $this->add([
            'name' => 'options[proposal][lang]',
            'type'  => ZElement\Select::class,
            'required'  => false,
            'options' => [
                'label'   => '',
                'options' => [
                    'ru' => 'Русский',
                    'en' => 'Английский',
                    'de' => 'Немецкий',
                ],
            ],
        ]);

        $this->add([
            'name' => 'options[hotels][days_count]',
            'type'  => PElement\NumbersList::class,
            'required'  => false,
            'options' => [
                'empty'   => 'Авторассчет',
                'label'   => 'Кол-во дней ',
            ],
        ]);

        $this->add([
            'name'  => 'clients',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields' => $fields = [
                    'client_id' => [
                        'name'    => 'Клиент',
                        'width'   => '250',
                        'options' => Client::getEntityCollection(),
                        'module'  => 'clients',
                        'placeholder' => 'Не выбран',
                    ],
                    'paid' => [
                        'name'  => 'Оплата',
                        'width' => '120',
                        'options' => [0 => 'Не оплачено', 1 => 'Оплачено'],
                    ],
                    'payment_type' => [
                        'name'  => 'Тип оплаты',
                        'width' => '195',
                        'options' => OrderConstants::$paymentType,
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('clients'),
            ],
        ]);

        $this->add([
            'name'  => 'hotels',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields' => $fields = [
                    'hotel_id' => [
                        'name'    => 'Гостиница',
                        'width'   => '190',
                        'options' => \Hotels\Admin\Model\Hotel::getEntityCollection(),
                        'class'   => 'hotel-id',
                        'placeholder' => 'Не выбран',
                    ],
                    'room_id' => [
                        'name'    => 'Номер',
                        'width'   => '200',
                        'options' => \Hotels\Admin\Model\HotelRoom::getEntityCollection(),
                        'class'   => 'room-id',
                        'placeholder' => 'Не выбран',
                    ],
                    'tourists' => [
                        'name'    => 'Гостей',
                        'width'   => '80',
                    ],
                    'breakfast' => [
                        'name'    => 'Завтрак',
                        'width'   => '150',
                        'class'   => 'breakfast',
                        'options' => [
                            \Hotels\Admin\Model\Hotel::BREAKFAST_NO          => 'Нет',
                            \Hotels\Admin\Model\Hotel::BREAKFAST_BUFFET      => 'Полн. завтрак',
                            \Hotels\Admin\Model\Hotel::BREAKFAST_CONTINENTAL => 'Континентальный',
                        ],
                    ],
                    'bed_size' => [
                        'name'    => 'Тип кровати',
                        'width'   => '190',
                        'class'   => 'bed_size',
                        'options' => [
                            1 => \Hotels\Admin\Model\HotelRoom::$bedSizes[1],
                            2 => \Hotels\Admin\Model\HotelRoom::$bedSizes[2],
                        ],
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('hotels'),
            ],
        ]);

        return $this;
    }

    public function setFilters()
    {
        $factory = new InputFactory();
        $filter = $this->getInputFilter();

        $filter->add($factory->createInput([
            'name'     => 'days',
            'filters'  => [new PFilter\FArray()],
        ]));

        $filter->add($factory->createInput([
            'name'     => 'proposal',
            'filters'  => [],
        ]));

        parent::setFilters();

        return $this;
    }
}