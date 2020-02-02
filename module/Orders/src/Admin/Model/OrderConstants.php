<?php
namespace Orders\Admin\Model;

class OrderConstants
{
    static public $colors = [
        '#3939f2' => 'Синий',
        '#cf2666' => 'Розовый',
        '#00ba23' => 'Зеленый',
        '#db7e10' => 'Оранжевый',
        '#e5002b' => 'Красный',
        '#2c6ff7' => 'Голубой',
        '#d91fc5' => 'Фиолетовый',
        '#94b510' => 'Салатовый',
        '#3c3c3c' => 'Черный',
        '#00747e' => 'Бирюзовый',
    ];

    const STATUS_NEW      = 0;
    const STATUS_PROCESS  = 1;
    const STATUS_CANCELED = 3;

    static public $statuses = [
        self::STATUS_NEW        => 'Новый заказ',
        self::STATUS_PROCESS    => 'В работе',
        self::STATUS_CANCELED   => 'Отменен',
    ];

    const PAYMENT_TYPE_NAL  = 1;
    const PAYMENT_TYPE_ORG  = 2;

    static public $paymentType = [
        0                         => 'Не выбран',
        self::PAYMENT_TYPE_NAL    => 'Наличный расчет',
        self::PAYMENT_TYPE_ORG    => 'Безналичный расчет',
    ];

    const CLIENT_TYPE_CLIENT  = 0;
    const CLIENT_TYPE_AGENCY  = 1;

    static public $clientTypes = [
        self::CLIENT_TYPE_CLIENT   => 'Физ. лицо',
        self::CLIENT_TYPE_AGENCY   => 'Турагенство',
    ];
}