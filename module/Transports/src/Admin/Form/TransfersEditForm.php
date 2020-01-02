<?php
namespace Transports\Admin\Form;

use Application\Admin\Model\Settings;
use Pipe\Form\Form\Admin\Form;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class TransfersEditForm extends Form
{
    public function init()
    {
        $this->addCommonElements(['id']);

        $this->add([
            'name' => 'name',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Название трансфера',
            ],
        ]);

        $this->add([
            'name' => 'duration',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Длительность трансфера',
            ],
        ]);

        $this->add([
            'name'  => 'guides',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'lang_id' => [
                        'name' => 'Язык',
                        'width'   => '200',
                        'options' => Settings::getInstance()->plugin('languages'),
                    ],
                    'income' => [
                        'name'  => 'Доход',
                        'width' => '100',
                    ],
                    'outgo' => [
                        'name'  => 'Расход',
                        'width' => '100',
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('guides'),
                'sort'  => false,
            ],
        ]);

        $this->add([
            'name'  => 'transport',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'transport_id' => [
                        'name'    => 'Транспорт',
                        'width'   => '200',
                        'options' => \Transports\Admin\Model\Transport::getEntityCollection(),
                    ],
                    'income' => [
                        'name'  => 'Доход',
                        'width' => '100',
                    ],
                    'outgo' => [
                        'name'  => 'Расход',
                        'width' => '100',
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('transport'),
                'sort'  => false,
            ],
        ]);
    }
}