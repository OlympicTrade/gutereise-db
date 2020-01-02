<?php
namespace Hotels\Admin\Model;

use Pipe\DateTime\Date;
use Pipe\Db\Entity\Entity;
use Hotels\Admin\Form\HotelsRoomEditForm;

class HotelRoom extends Entity
{
    static $bedSizes = [
        0 => 'Любой тип',
        1 => 'Односпальные кровати',
        2 => 'Двуспальная кровать',
    ];

    static public function getFactoryConfig()
    {
        return [
            'table'      => 'hotels_rooms',
            'properties' => [
                'depend'      => [],
                'name'        => [],
                'capacity'    => ['default' => 1],
                'bed_size'    => ['default' => 1],
            ],
            'plugins' => [
                'price' => function($model) {
                    $list = HotelRoomPrice::getEntityCollection();
                    $list->select()->order('tourists ASC');
                    return $list;
                },
            ]
        ];
    }
    public function getForm()
    {
        $form = new HotelsRoomEditForm();
        $form->setOptions(['model' => $this]);
        $form->init();
        $form->setDataFromModel();



        /*$form = new HotelsRoomEditForm();
        $form->setOptions(['model' => $this]);
        $form->init();

        $form->setData($this->serializeArray([], 'rooms[' . $this->id() . ']'));*/

        return $form;
    }

    public function getPrice($tourists, $date)
    {
        $date = Date::getDT($date);
        $price = new HotelRoomPrice();

        $price->select()
            ->order('tourists DESC')
            ->where
            ->equalTo('depend', $this->id())
            ->lessThanOrEqualTo('tourists', $tourists)
            ->nest()
                ->nest()
                    ->lessThanOrEqualTo('date_from', $date->format('0000-m-d'))
                    ->greaterThanOrEqualTo('date_to', $date->format('0000-m-d'))
                    ->equalTo('date_reverse', 0)
                ->unnest()
                ->or
                ->nest()
                    ->nest()
                        ->lessThanOrEqualTo('date_from', $date->format('0000-m-d'))
                        ->or
                        ->greaterThanOrEqualTo('date_to', $date->format('0000-m-d'))
                    ->unnest()
                    ->equalTo('date_reverse', 1)
                ->unnest()
            ->unnest();

        return $price->get('price');
    }
}