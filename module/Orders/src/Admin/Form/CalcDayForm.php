<?php
namespace Orders\Admin\Form;

use Pipe\Form\Form\Admin\Form;

use Transports\Admin\Model\Transfer;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class CalcDayForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'day_id',
            'type'  => ZElement\Hidden::class,
            'attributes'=>[
                'class' => 'day-id',
            ],
        ]);

        $this->add([
            'name' => '[proposal][title]',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Место начала экурсии',
            ],
            'attributes'=>[
                'class' => 'proposal_place_start std-input',
            ],
        ]);

        $this->add([
            'name' => '[proposal][place]',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Место начала экурсии',
            ],
            'attributes'=>[
                'class' => 'proposal_place_start std-input',
                'data-type' => 'address',
                'placeholder' => 'Введите местоположение',
            ],
        ]);

        $this->add([
            'name' => '[proposal][desc]',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Описание экскурсии',
            ],
            'attributes'=>[
                'class' => 'proposal_desc std-input',
            ],
        ]);

        $this->add([
            'name' => 'transfer_time',
            'type'  => PElement\Time::class,
            'options' => [
                'label' => 'Время трансфера',
                'min' => '00:15',
            ],
            'attributes'=>[
                'class' => 'std-select transfer_time',
            ],
        ]);
        $this->get('transfer_time')->setValue('01:00:00');

        $this->add([
            'name' => 'car_delivery_time',
            'type'  => PElement\Time::class,
            'options' => [
                'label' => 'Время подачи',
            ],
            'attributes'=>[
                'class' => 'std-select car_delivery_time',
            ],
        ]);
        $this->get('car_delivery_time')->setValue('01:00:00');

        $this->add([
            'name' => 'transport_autocomplete',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Транспорт',
            ],
            'attributes'=>[
                'class' => 'std-input transport-ac',
            ],
        ]);

        $this->add([
            'name' => 'museums_autocomplete',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Музей',
            ],
            'attributes'=>[
                'class' => 'std-input museums-ac',
            ],
        ]);

        /*$this->add([
            'name' => 'transport_type',
            'type' => ZElement\Hidden::class,
            'attributes'=>[
                'class' => 'std-select transport-type',
            ],
        ]);
        $this->get('transport_type')->setValue(2);*/

        $opts = ['-1' => 'Авторассчет'];
        for ($i = 0; $i <= 100; $i += 5) {
            $opts[$i] = $i . '%';
        }
        $this->add([
            'name' => 'margin',
            'type'  => ZElement\Select::class,
            'options' => [
                'label' => 'Наценка',
                'options' => $opts,
            ],
            'attributes'=>[
                'class' => 'std-select margin',
            ],
        ]);

        $this->add([
            'name' => 'date',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Дата',
            ],
            'attributes'=>[
                'class' => 'datepicker std-input date',
            ],
        ]);

        $this->add([
            'name' => 'time',
            'type'  => PElement\Time::class,
            'options' => [
                'label' => 'Время начала',
            ],
            'attributes'=>[
                'class' => 'time_from std-select',
            ],
        ]);

        $this->add([
            'name' => '[proposal][place_start]',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Адрес начала экскурсии'
            ],
            'attributes'=>[
                'data-type' => 'address',
                'class' => 'proposal_place_start std-input',
            ],
        ]);

        $this->add([
            'name' => '[proposal][place_end]',
            'type'  => ZElement\Text::class,
            'options' => [
                'label' => 'Адрес окончания экскурсии'
            ],
            'attributes'=>[
                'data-type' => 'address',
                'class' => 'proposal_place_end std-input',
            ],
        ]);

        $this->add([
            'name' => '[guides-calc][duration]',
            'type'  => PElement\Time::class,
            'options' => [
                'label'   => 'Время гида',
                'empty'   => 'Авторассчет',
                'options' => ['00:00:00' => 'Без гида'],
                'min'     => '00:15',
                'max'     => '12:00',
            ],
            'attributes'=>[
                'class' => 'std-select guides-duration',
            ],
        ]);

        $this->add([
            'name' => '[guides-calc][count]',
            'type'  => 'Pipe\Form\Element\NumbersList',
            'options' => [
                'label' => 'Кол-во гидов',
                'empty' => 'Авторассчет',
                'min' => 1,
                'max' => 10,
            ],
            'attributes'=>[
                'class' => 'std-select guides-count',
            ],
        ]);

        /*$this->add([
            'name' => '[guides-calc][transfer_id]',
            'type'  => 'Pipe\Form\Element\TreeSelect',
            'options' => [
                'label'      => 'Трансфер',
                //'empty'      => 'Нет трансфера',
                'before'  => ['auto' => 'Авторассчет', '0' => 'Нет трансфера'],
                'options' => \Transports\Admin\Model\Transfer::getEntityCollection(),
            ],
            'attributes'=>[
                'class' => 'std-select guides-transfer_id',
            ],
        ]);*/

        $this->add([
            'name' => 'transfer_id',
            'type'  => PElement\ESelect::class,
            'options' => [
                'label'      => 'Трансфер',
                'empty'      => 'Не выбран',
                'model' => new Transfer(),
            ],
            'attributes'=>[
                'class' => 'std-select transfer_id',
            ],
        ]);

        $this->add([
            'name' => '[extra][autocalc]',
            'type'  => ZElement\Checkbox::class,
            'options' => [
                'label' => 'Авторассчет',
                'checked_value'   => '1',
                'unchecked_value' => '0',
            ],
            'attributes'=>[
                'class' => 'extra_calc',
            ],
        ]);
        $this->get('[extra][autocalc]')->setChecked(true);

        $this->add([
            'name' => '[proposal][pricetable][autocalc]',
            'type'  => ZElement\Checkbox::class,
            'options' => [
                'label' => 'Авторассчет',
                'checked_value'   => '1',
                'unchecked_value' => '0',
            ],
            'attributes'=>[
                'class' => 'proposal_calc_pricetable',
            ],
        ]);
        $this->get('[proposal][pricetable][autocalc]')->setChecked(true);

        $this->add([
            'name' => '[proposal][timetable][autocalc]',
            'type'  => ZElement\Checkbox::class,
            'options' => [
                'label' => 'Авторассчет',
                'checked_value'   => '1',
                'unchecked_value' => '0',
            ],
            'attributes'=>[
                'class' => 'proposal_calc_timetable',
            ],
        ]);
        $this->get('[proposal][timetable][autocalc]')->setChecked(true);

        return $this;
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $this->setInputFilter($inputFilter);
    }
}