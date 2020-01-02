<?php
namespace Orders\Admin\Form;

use Application\Admin\Model\Currency;
use Application\Admin\Model\Language;
use Application\Admin\Model\Settings;
use Pipe\Form\Form\Admin\Form;

use Orders\Admin\Model\OrderConstants;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class CalcForm extends Form
{
    public function __construct()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add([
            'name' => 'order_id',
            'type'  => ZElement\Hidden::class,
        ]);

        $this->add([
            'name' => 'day_id',
            'type'  => ZElement\Hidden::class,
        ]);

        $this->add([
            'name' => 'excursion_id',
            'type'  => ZElement\Hidden::class,
        ]);

        $this->add([
            'name' => 'tours_autocomplete',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Экскурсия или тур',
            ],
            'attributes'=>[
                'class' => 'std-input tours-ac',
            ],
        ]);

        $this->add([
            'name'  => 'agency',
            'type' => ZElement\Select::class,
            'options' => [
                'options' => OrderConstants::$clientTypes,
                'label' => 'Клиент',
            ]
        ]);

        $this->add([
            'name'  => 'currency',
            'type' => ZElement\Select::class,
            'options' => [
                'options' => Currency::$currencyTypes,
                'label' => 'Валюта',
            ]
        ]);

        $this->add([
            'name'  => 'currency_rate',
            'type' => ZElement\Text::class,
            'options' => [
                'label' => 'Курс',
            ],
            'attributes'=>[
                'placeholder' => 'Авто',
            ],
        ]);

        $this->add([
            'name'  => 'lang_id',
            'type' => PElement\ESelect::class,
            'options' => [
                'label'   => 'Язык экскурсии',
                'sort'    => 'id',
                'options' => Language::getEntityCollection(),
            ],
            'attributes' => [
                'value' => 'ru'
            ],
        ]);

        $this->add([
            'name'  => 'kp_lang',
            'type' => ZElement\Select::class,
            'options' => [
                'options' => ['ru' => 'Русский', 'en' => 'Английский', 'de' => 'Немецкий'],
                'label' => 'Язык КП',
            ]
        ]);

        $this->add([
            'name' => 'adults',
            'type'  => 'Zend\Form\Element\Number',
            'options' => [
                'label' => 'Взрослых',
            ],
        ]);

        $this->add([
            'name' => 'children',
            'type'  => 'Zend\Form\Element\Number',
            'options' => [
                'label' => 'Детей',
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