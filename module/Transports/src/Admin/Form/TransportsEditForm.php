<?php
namespace Transports\Admin\Form;

use Pipe\Form\Form\Admin\Form;
use Transports\Admin\Model\Transport;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class TransportsEditForm extends Form
{
    public function init()
    {
        $this->addCommonElements(['id']);

        $this->add(array(
            'name' => 'name',
            'type'  => ZElement\Text::class,
            'options' => array(
                'label' => 'Название',
            ),
            'attributes' => [
                'placeholder' => 'Автобус',
            ],
        ));

        $this->add(array(
            'name' => 'type',
            'type'  => ZElement\Select::class,
            'options' => array(
                'label' => 'Тип',
                'options'   => Transport::$types,
            )
        ));

        $this->add(array(
            'name' => 'capacity',
            'type'  => ZElement\Text::class,
            'options' => array(
                'label' => 'Макс. пассажиров',
            )
        ));

        $this->add(array(
            'name' => 'min_price',
            'type'  => ZElement\Text::class,
            'options' => array(
                'label' => 'Мин. стоимость',
            )
        ));

        $this->add(array(
            'name' => 'comment',
            'type'  => ZElement\Textarea::class,
            'options' => array(
                'label' => 'Комментарий',
            ),
        ));

        $this->add([
            'name' => 'genitive1',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Склонение 1',
            ],
            'attributes' => [
                'placeholder' => 'аренда "автобуса"',
            ],
        ]);

        $this->add([
            'name' => 'genitive2',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Склонение 2',
            ],
            'attributes' => [
                'placeholder' => 'аренда двух "автобусов"',
            ],
        ]);

        $this->add([
            'name'  => 'drivers',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'driver_id' => [
                        'name'  => 'Водитель',
                        'width' => '300',
                        'options' => \Drivers\Admin\Model\Driver::getEntityCollection(),
                        'module'  => 'drivers',
                    ],
                    'price_day' => [
                        'name'  => 'Цена в час (день)',
                        'width' => '150',
                    ],
                    'price_night' => [
                        'name'  => '(ночь)',
                        'width' => '100',
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('drivers'),
            ],
        ]);

        $this->add([
            'name'  => 'price',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'count' => [
                        'name'    => 'Пассажиров от',
                        'width'   => '120',
                        'default' => '1',
                    ],
                    'price_day' => [
                        'name'  => 'Цена в час (день)',
                        'width' => '150',
                    ],
                    'price_night' => [
                        'name'  => '(ночь)',
                        'width' => '100',
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('price'),
            ],
        ]);
    }
}