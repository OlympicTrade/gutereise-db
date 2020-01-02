<?php
namespace Clients\Admin\Form;

use Pipe\Form\Form\Admin\Form;
use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class ClientsEditForm extends Form
{
    public function init()
    {
        $this->addCommonElements(['id', 'name', 'contacts', 'comment', 'company_details']);

        $this->add([
            'name'  => 'employees',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'name'  => [
                        'name'  => 'ФИО',
                        'width' => '150',
                    ],
                    'job'  => [
                        'name'  => 'Должность',
                        'width' => '150',
                    ],
                    'phone_1'  => [
                        'name'  => 'Телефон 1',
                        'width' => '130',
                    ],
                    'phone_2'  => [
                        'name'  => 'Телефон 1',
                        'width' => '130',
                    ],
                    'email'  => [
                        'name'  => 'E-mail',
                        'width' => '150',
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('employees'),
            ],
        ]);
    }
}