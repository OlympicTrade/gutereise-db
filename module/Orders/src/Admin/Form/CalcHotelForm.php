<?php
namespace Orders\Admin\Form;

use Pipe\Form\Form\Admin\Form;

use Hotels\Admin\Model\Hotel;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class CalcHotelForm extends Form
{
    /**
     * @var Hotel
     */
    protected $hotel;

    public function setOptions($options = [])
    {
        $options['baseName'] = '';
        $this->hotel = $options['hotel'];

        return parent::setOptions($options);
    }

    public function init()
    {
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add([
            'name' => 'id',
            'type'  => ZElement\Hidden::class,
            'attributes'=>[
                'class' => 'hotel-id',
            ],
        ]);

        $rooms = $this->hotel->plugin('rooms');

        $this->add([
            'name' => '[id]',
            'type'  => 'Pipe\Form\Element\TreeSelect',
            'options' => [
                'label'   => 'Номер',
                'empty'   => 'Не выбран',
                'options' => $rooms
            ],
            'attributes'=>[
                'class' => 'hotel-room-id std-select',
            ],
        ]);

        $this->add([
            'name' => '[tourists]',
            'type'  => ZElement\Text::class,
            'options' => [
                'label'   => 'Туристов',
            ],
            'attributes'=>[
                'class' => 'hotel-room-tourists std-input',
                'placeholder' => ' '
            ],
        ]);

        $this->add([
            'name' => '[breakfast]',
            'type'  => ZElement\Select::class,
            'options' => [
                'label'   => 'Завтрак',
                'options'   => $this->hotel->getBreakfastOpts()
            ],
            'attributes'=>[
                'class' => 'hotel-room-tourists std-select',
            ],
        ]);

        $this->add([
            'name' => '[bed_size]',
            'type'  => ZElement\Select::class,
            'options' => [
                'label'   => 'Кровати',
                'options'   => [
                    1 => 'Односпальные',
                    2 => 'Двуспальные',
                ],
            ],
            'attributes'=>[
                'class' => 'hotel-room-bed std-select',
            ],
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $this->setInputFilter($inputFilter);
    }
}