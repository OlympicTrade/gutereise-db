<?php
namespace Orders\Admin\Form;

use Pipe\Form\Form\Admin\Form;

use Drivers\Admin\Model\Driver;
use Museums\Admin\Model\Museum;
use Transports\Admin\Model\Transfer;
use Transports\Admin\Model\Transport;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class OrdersDayEditForm extends Form
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
            'name' => 'date',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Дата',
            ],
            'attributes'=>[
                'class' => 'datepicker std-input',
                'data-name'  => 'date',
                'data-field' => 'date',
            ],
        ]);

        $this->add([
            'name' => 'time',
            'type'  => PElement\Time::class,
            'options' => [
                'label' => 'Время начала',
            ],
            /*'attributes'=>[
                'data-name' => 'time',
            ],*/
        ]);

        $this->add([
            'name' => 'transfer_time',
            'type'  => PElement\Time::class,
            'options' => [
                'label' => 'Время трансфера',
                'options' => [
                    '00:15:00' => '00:15',
                    '00:30:00' => '00:30',
                    '00:45:00' => '00:45',
                ],
                'min' => '01:00',
                'max' => '05:00'
            ],
        ]);

        $this->add([
            'name' => 'car_delivery_time',
            'type'  => PElement\Time::class,
            'options' => [
                'label' => 'Время подачи',
            ],
        ]);

        $opts = ['-1' => 'Авторассчет'];
        for ($i = 0; $i <= 100; $i += 5) {
            $opts[$i] = $i . '%';
        }
        $this->add([
            'name' => 'margin',
            'type'  => ZElement\Select::class,
            'options' => [
                'label'   => 'Наценка',
                'options' => $opts,
            ],
            'attributes'=>[
                'class' => 'std-select margin',
            ],
        ]);

        $this->add([
            'name' => 'transfer_id',
            'type'  => PElement\ESelect::class,
            'options' => [
                'label'      => 'Трансфер',
                'empty'      => 'Не выбран',
                'model' => new Transfer(),
            ],
            'attributes'=>[
                'class' => 'std-select',
            ],
        ]);

        $this->add([
            'name' => 'duration',
            'type'  => PElement\Time::class,
            'options' => [
                'label' => 'Длительность',
                'min' => '00:15',
                'max' => '12:00',
            ],
            'attributes'=>[
                'class' => 'std-select duration',
            ],
        ]);

        $this->add([
            'name' => '[options][proposal][place_start]',
            'type'  => ZElement\Textarea::class,
            'options' => [
                'label' => 'Место начала экскурсии',
            ],
            'attributes'=>[
                'placeholder' => '',
                'class'       => 'std-textarea short',
                'data-type'   => 'address',
            ],
        ]);

        $this->add([
            'name' => '[options][proposal][place_end]',
            'type'  => ZElement\Textarea::class,
            'options' => [
                'label' => 'Место окончания экскурсии',
            ],
            'attributes'=>[
                'placeholder' => '',
                'class'       => 'std-textarea short',
                'data-type'   => 'address',
            ],
        ]);

        $this->add([
            'name' => '[options][proposal][pricetable][text]',
            'type'  => ZElement\Textarea::class,
            'options' => [
                'label' => 'В стоимость включено',
            ],
            'attributes'=>[
                'class' => 'proposal_pricetable std-textarea',
            ],
        ]);

        $this->add([
            'name' => '[options][proposal][timetable][text]',
            'type'  => ZElement\Textarea::class,
            'options' => [
                'label' => 'Расписание экскурсии',
            ],
            'attributes'=>[
                'class' => 'proposal_timetable std-textarea',
            ],
        ]);

        $this->add([
            'name' => '[options][proposal][timetable][autocalc]',
            'type'  => ZElement\Checkbox::class,
            'options' => [
                'label' => 'Авторассчет',
                'checked_value'   => '1',
                'unchecked_value' => '0',
            ],
            'attributes'=>[
                'class' => 'autocalc proposal_calc_timetable',
            ],
        ]);
        $this->get('[options][proposal][timetable][autocalc]')->setChecked(true);

        $this->add([
            'name' => '[options][proposal][pricetable][autocalc]',
            'type'  => ZElement\Checkbox::class,
            'options' => [
                'label' => 'Авторассчет',
                'checked_value'   => '1',
                'unchecked_value' => '0',
            ],
            'attributes'=>[
                'class' => 'autocalc proposal_calc_pricetable',
            ],
        ]);
        $this->get('[options][proposal][pricetable][autocalc]')->setChecked(true);

        $this->add([
            'name' => '[options][extra][autocalc]',
            'type'  => ZElement\Checkbox::class,
            'options' => [
                'label' => 'Авторассчет',
                'checked_value'   => '1',
                'unchecked_value' => '0',
            ],
            'attributes'=>[
                'class' => 'autocalc extra_autocalc',
            ],
        ]);

        $this->add([
            'name' => 'comment',
            'type'  => ZElement\Textarea::class,
            'options' => [
                'label' => 'Комментарий',
            ],
            'attributes'=>[
                'class' => 'autocalc',
            ],
        ]);

        $this->add([
            'name' => '[options][guides][autocalc]',
            'type'  => ZElement\Checkbox::class,
            'options' => [
                'label' => 'Авторассчет стоимости',
                'checked_value'   => 1,
                'unchecked_value' => 0,
            ],
            'attributes'=>[
                'class' => 'autocalc autocalc-guides',
            ],
        ]);

        $this->add([
            'name' => '[options][museums][autocalc]',
            'type'  => ZElement\Checkbox::class,
            'options' => [
                'label' => 'Авторассчет стоимости',
                'checked_value'   => 1,
                'unchecked_value' => 0,
            ],
            'attributes'=>[
                'class' => 'autocalc autocalc-museums',
            ],
        ]);

        $this->add([
            'name' => '[options][transports][autocalc]',
            'type'  => ZElement\Checkbox::class,
            'options' => [
                'label' => 'Авторассчет стоимости',
                'checked_value'   => 1,
                'unchecked_value' => 0,
            ],
            'attributes'=>[
                'class' => 'autocalc autocalc-transports',
            ],
        ]);

        $this->add([
            'name'  => '[timetable]',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'duration' => [
                        'name'  => 'Длительность',
                        'width' => '120',
                        'type'  => 'time',
                        'options'   => [
                            'min' => '00:00',
                            'max' => '12:00',
                        ],
                    ],
                    'name' => [
                        'name'  => 'Название',
                        'width' => '500',
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('timetable'),
                'form'    => 'timetable',
            ],
        ]);

        $this->add([
            'name'  => '[pricetable]',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'name' => [
                        'name'  => 'Название',
                        'width' => '620',
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('pricetable'),
                'form'    => 'pricetable',
            ],
        ]);

        $this->add([
            'name'  => '[guides]',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields' => $fields = [
                    'guide_id' => [
                        'name'  => 'Гид',
                        'width' => '250',
                        'placeholder' => 'Не выбран',
                        'options' => \Guides\Admin\Model\Guide::getEntityCollection(),
                        'module'  => 'guides',
                        'class'   => 'guide-id',
                    ],
                    'duration' => [
                        'name'  => 'Время',
                        'width' => '75',
                        'type'  => 'time',
                        'options'   => [
                            'min' => '00:00',
                            'max' => '12:00',
                        ],
                    ],
                    'income' => [
                        'name'  => 'Доход',
                        'width' => '75',
                        'class'   => 'income',
                    ],
                    'outgo' => [
                        'name'  => 'Расход',
                        'width' => '75',
                        'class'   => 'outgo',
                    ],
                    'paid' => [
                        'name'  => 'Оплата',
                        'width' => '110',
                        'options' => [0 => 'Не оплачено', 1 => 'Оплачено'],
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('guides'),
                'form'    => 'guides',
                //'btns'    => [['class' => 'guides-update', 'icon' => 'fa fa-sync']]
            ],
        ]);

        $this->add([
            'name'  => '[museums]',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'museum_id' => [
                        'attrs'   => ['data-field' => 'museum_id'],
                        'name'    => 'Музей',
                        'width'   => '250',
                        'placeholder' => 'Не выбран',
                        'options' => Museum::getEntityCollection(),
                        'module'  => 'museums',
                    ],
                    'duration' => [
                        'name'  => 'Время',
                        'width' => '75',
                        'type'  => 'time',
                        'options'   => [
                            'min' => '00:00',
                            'max' => '8:00',
                        ],
                    ],
                    'tickets_adults' => [
                        'name'  => 'Билеты дет.',
                        'width' => '100',
                        'class'   => 'tickets_adults',
                    ],
                    'tickets_children' => [
                        'name'  => 'Билеты врз.',
                        'width' => '100',
                        'class'   => 'tickets_children',
                    ],
                    'guides' => [
                        'name'  => 'Гид',
                        'width' => '75',
                        'class'   => 'guides',
                    ],
                    'extra' => [
                        'name'  => 'Прочее',
                        'width' => '75',
                        'class'   => 'extra',
                    ],
                    'outgo' => [
                        'name'  => 'Расход',
                        'width' => '75',
                        'class'   => 'outgo',
                    ],
                    'paid' => [
                        'name'  => 'Оплата',
                        'width' => '120',
                        'options' => [0 => 'Не оплачено', 1 => 'Оплачено'],
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('museums'),
                'form'    => 'museums',
            ],
        ]);

        $this->add([
            'name'  => '[transports]',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'transport_id' => [
                        'name'  => 'Транспорт',
                        'width' => '190',
                        'placeholder' => 'Не выбран',
                        'options' => Transport::getEntityCollection(),
                        'module'  => 'transports',
                        //'class'   => 'transport-id',
                        'attrs'   => ['data-field' => 'transport_id'],
                    ],
                    'driver_id' => [
                        'name'  => 'Водитель',
                        'width' => '190',
                        'placeholder' => 'Не выбран',
                        'options' => Driver::getEntityCollection(),
                        'module'  => 'drivers',
                        //'class'   => 'driver-id',
                        'attrs'   => ['data-field' => 'driver_id'],
                    ],
                    'duration' => [
                        'name'  => 'Время',
                        'width' => '75',
                        'type'  => 'time',
                        'options'   => [
                            'min' => '00:00',
                            'max' => '24:00',
                        ],
                    ],
                    'passengers' => [
                        'name'  => 'Пассажиров',
                        'width' => '100',
                    ],
                    'income' => [
                        'name'  => 'Доход',
                        'width' => '75',
                        'class'   => 'income',
                    ],
                    'outgo' => [
                        'name'  => 'Расход',
                        'width' => '75',
                        'class'   => 'outgo',
                    ],
                    'paid' => [
                        'name'  => 'Оплата',
                        'width' => '120',
                        'options' => [0 => 'Не оплачено', 1 => 'Оплачено'],
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('transports'),
                'form'    => 'transports',
            ],
        ]);

        $this->add([
            'name'  => '[extra]',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'name' => [
                        'name'  => 'Название',
                        'width' => '250',
                    ],
                    'proposal_name' => [
                        'name'  => 'Название для КП',
                        'width' => '300',
                    ],
                    'per_person' => [
                        'name'  => 'За человека',
                        'width' => '110',
                    ],
                    'income' => [
                        'name'  => 'Полностью',
                        'width' => '105',
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

    /*public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput([
            'name'     => '[extra]',
            'filters'  => [new FArray()],
        ]));

        $inputFilter->add($factory->createInput([
            'name'     => '[transports]',
            'filters'  => [new FArray()],
        ]));

        $inputFilter->add($factory->createInput([
            'name'     => '[museums]',
            'filters'  => [new FArray()],
        ]));

        $inputFilter->add($factory->createInput([
            'name'     => '[guides]',
            'filters'  => [new FArray()],
        ]));

        $inputFilter->add($factory->createInput([
            'name'     => '[pricetable]',
            'filters'  => [new FArray()],
        ]));

        $inputFilter->add($factory->createInput([
            'name'     => '[timetable]',
            'filters'  => [new FArray()],
        ]));

        $inputFilter->add($factory->createInput([
            'name' => 'adults',
            'required' => false,
        ]));

        $inputFilter->add($factory->createInput([
            'name' => 'children',
            'required' => false,
        ]));

        $this->setInputFilter($inputFilter);
    }*/
    /*public function setDataFromModel()
    {
        $model = $this->getModel();

        foreach ($this as $element) {
            $elName = $element->getName();

            if($this->prefix) {
                $elName = substr($elName, strlen($this->prefix));
            }

            $elName = ltrim($elName, '[');
            $elName = str_replace(']', '', $elName);

            $trace = explode('[', $elName);
            $traceCount = count($trace);

            if($this->prefix) {
                //dd($elName);
            }

            d($elName . ' - ');

            $value = $model;
            for($i = 0; $i < $traceCount; $i++) {
                $tName = $trace[$i];

                if($value instanceof Entity) {
                    if($value->hasProperty($tName)) {
                        $value = $value->$tName;
                    } elseif($value->hasPlugin($tName)) {
                        $value = $value->$tName();
                    } else {
                        $value = null;
                        break;
                    }
                } elseif($value instanceof EntityCollection) {
                    foreach ($value as $row) {
                        if($row->id() == $tName) {
                            $value = $row;
                            break;
                        }
                    }
                    if($value instanceof EntityCollection) {
                        $value = null;
                        break;
                    }
                } elseif(is_array($value) || $value instanceof \ArrayAccess) {
                    $value = $value[$tName];
                } elseif($value === null) {
                    break;
                } else {
                    throw new \Exception(
                        'Unknown value type. '.
                        'el full name: ' . $element->getName() . ', '.
                        'el short name: ' . $elName . ', '.
                        'step: ' . $tName . ', '.
                        'value: "' . (is_object($value) ? get_class($value) : $value) . '"'
                    );
                }
            }

            if($element instanceof EntityAware) {
                $element->setValue($value);
            } else {
                if($value instanceof Entity || $value instanceof EntityCollection) {
                    $element->setValue($value->serrializeArray(1));
                } else {
                    $element->setValue($value);
                }
            }
        }
        dd();
    }*/
}