<?php
namespace Orders\Admin\Model;

use Pipe\Db\Entity\Entity;
use Orders\Admin\Form\OrdersDayEditForm;

class OrderDay extends Entity
{
    static public function getFactoryConfig()
    {
        return [
            'table'      => 'orders_days',
            'properties' => [
                'depend'        => [],
                'day_id'        => [],
                'date'          => ['type' => Entity::PROPERTY_TYPE_DATE],
                'margin'        => [],
                'time'          => ['type' => Entity::PROPERTY_TYPE_TIME],
                'transfer_time' => [],
                'transfer_id'   => ['type' => Entity::PROPERTY_TYPE_NUM],
                'car_delivery_time'  => [],
                'duration'      => [],
                'options'       => ['type' => Entity::PROPERTY_TYPE_JSON],
            ],
            'plugins'    => [
                'timetable' => function($model) {
                    $timetable = OrderDayTimetable::getEntityCollection();
                    $timetable->select()->order('sort');
                    return $timetable;
                },
                'pricetable' => function($model) {
                    $pricetable = OrderDayPricetable::getEntityCollection();
                    $pricetable->select()->order('sort');
                    return $pricetable;
                },
                /*'attrs' => function($model) {
                    $attrs = new \Pipe\Db\Plugin\Attributes();
                    $attrs->setTable('orders_days_attrs');
                    return $attrs;
                },*/
                'guides' => function($model) {
                    $list = OrderDayGuides::getEntityCollection();
                    $list->select()->order('sort');
                    return $list;
                },
                'transports' => function($model) {
                    $list = OrderDayTransport::getEntityCollection();
                    $list->select()->order('sort');
                    return $list;
                },
                'museums' => function($model) {
                    $museums = OrderDayMuseums::getEntityCollection();
                    $museums->select()->order('sort');
                    return $museums;
                },
                'extra' => function($model) {
                    $extra = OrderDayExtra::getEntityCollection();
                    $extra->select()->order('sort');
                    return $extra;
                },
            ],
        ];
    }

    /**
     * @return OrdersDayEditForm
     * @throws \Exception
     */
    public function getForm()
    {
        $form = new OrdersDayEditForm();
        $form->setOptions(['model' => $this]);
        $form->init();
        $form->setDataFromModel();

        return $form;
    }

    /** @return bool|\DateTime */
    public function getDt()
    {
        return \DateTime::createFromFormat('Y-m-d', $this->get('date', ['filter' => true]));
    }
}