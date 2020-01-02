<?php
namespace Excursions\Admin\Form;

use Pipe\Form\Filter\FArray;
use Pipe\Form\Form\Admin\Form;
use Zend\InputFilter\Factory as InputFactory;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class ExcursionsEditForm extends Form
{
    public function init()
    {
        $this->addCommonElements(['id', 'name']);
        $opts = [];
        for ($i = 0; $i <= 100; $i += 5) {
            $opts[$i] = $i . '%';
        }

        $this->add([
            'name'  => 'margin',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'tourists'  => [
                        'name'  => 'Туристов от',
                        'width' => '105',
                        'default' => '1',
                    ],
                    'margin'  => [
                        'name'  => 'Наценка в %',
                        'width' => '150',
                        'options' => $opts,
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('margin'),
            ],
        ]);
    }

    public function setFilters()
    {
        parent::setFilters();

        $factory = new InputFactory();
        $filter = $this->getInputFilter();

        $filter->add($factory->createInput([
            'name'     => 'days',
            'filters'  => [new FArray()],
        ]));
    }
}