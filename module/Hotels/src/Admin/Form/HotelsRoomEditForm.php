<?php
namespace Hotels\Admin\Form;

use Pipe\Form\Form\Admin\Form;
use Hotels\Admin\Model\HotelRoom;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class HotelsRoomEditForm extends Form
{
    public function setOptions($options = [])
    {
        $this->setPrefix('rooms[' . $options['model']->id() . ']');

        return parent::setOptions($options);
    }

    public function init()
    {
        $this->addCommonElements(['id', 'name']);

        $this->add([
            'name' => 'capacity',
            'type'  => ZElement\Select::class,
            'options' => [
                'label'   => 'Кол-во гостей',
                'options' => [
                    1 => '1 гость',
                    2 => '2 гостя',
                    3 => '3 гостя',
                ],
            ],
        ]);

        $this->add([
            'name' => 'bed_size',
            'type'  => ZElement\Select::class,
            'options' => [
                'label'   => 'Размер кровати',
                'options' => HotelRoom::$bedSizes,
            ],
        ]);

        $this->add([
            'name'  => 'price',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => [
                    'price' => [
                        'name'  => 'Стоимость',
                        'width' => '160',
                    ],
                    'tourists' => [
                        'name'  => 'Туристов от',
                        'width' => '105',
                        'default' => '1',
                    ],
                    'date_from'  => [
                        'name'   => 'Действует с',
                        'width'  => '115',
                        'class'  => 'datepicker-dm',
                        'default' => '31.12',
                        'filter' => function($val) {
                            return $val->format('d.m');
                        },
                    ],
                    'date_to'  => [
                        'name'  => 'по (включительно)',
                        'width' => '160',
                        'class' => 'datepicker-dm',
                        'default' => '01.01',
                        'filter' => function($val) {
                            return $val->format('d.m');
                        },
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('price'),
                'form'    => 'price',
            ],
        ]);

        return $this;
    }
}