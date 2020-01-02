<?php
namespace Hotels\Admin\Form;

use Pipe\Form\Form\Admin\Form;
use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class HotelsEditForm extends Form
{
    public function init()
    {
        $this->addCommonElements(['id', 'name', 'contacts', 'comment', 'company_details']);

        $this->add([
            'name'  => 'breakfast',
            'type'  => PElement\EArray::class,
            'options' => [
                'elements' => [
                    'buffet' => [
                        'active' => [
                            'element'  => new PElement\Checkbox('_', ['label' => 'Шведский стол']),
                        ],
                        'price'  => 'Шведский стол (стоимость)',
                    ],
                    'continental' => [
                        'active' => [
                            'element'  => new PElement\Checkbox('_', ['label' => 'Континентальный завтрак']),
                        ],
                        'price'  => 'Континентальный завтрак (стоимость)',
                    ],
                ],
                'view' => [
                    [
                        ['width' => 50, 'element' => '[buffet][active]'],
                        ['width' => 50, 'element' => '[continental][active]'],
                    ],
                    [
                        ['width' => 50, 'element' => '[buffet][price]'],
                        ['width' => 50, 'element' => '[continental][price]'],
                    ],
                ]
            ],
        ]);
    }
}