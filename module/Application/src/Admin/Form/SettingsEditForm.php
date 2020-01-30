<?php
namespace Application\Admin\Form;

use Pipe\Form\Form\Admin\Form;
use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class SettingsEditForm extends Form
{
    public function init()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->addCommonElements(['id', 'contacts', 'company_details']);

        $this->add([
            'name'  => 'margin',
            'type'  => PElement\EArray::class,
            'options' => [
                'elements' => [
                    'client' => 'Наценка для клиентов (%)',
                    'agency' => 'Наценка для турагенств (%)',
                ],
                'view' => [
                    [
                        ['width' => 50, 'element' => '[client]'],
                        ['width' => 50, 'element' => '[agency]'],
                    ],
                ]
            ],
        ]);

        $this->add([
            'name'  => 'currency',
            'type'  => PElement\EArray::class,
            'options' => [
                'elements' => [
                    'eur' => 'Курс евро',
                    'usd' => 'Курс доллара',
                ],
                'view' => [
                    [
                        ['width' => 50, 'element' => '[eur]'],
                        ['width' => 50, 'element' => '[usd]'],
                    ],
                ]
            ],
        ]);

        $this->add([
            'name'  => 'languages',
            'type' => PElement\ECollection::class,
            'options' => [
                'fields' => $fields = [
                    'name' => [
                        'name'    => 'Назвние',
                        'width'   => '150',
                    ],
                    '[declension][5]' => [
                        'name'    => 'Твор. падеж',
                        'width'   => '150',
                    ],
                    'code' => [
                        'name'  => 'Код',
                        'width' => '60',
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('languages'),
                'sort'   => false,
            ],
        ]);

        return $this;
    }
}