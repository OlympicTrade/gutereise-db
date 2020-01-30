<?php
namespace Orders\Admin\Model;

use Application\Admin\Model\Currency;
use Application\Admin\Model\Language;
use Managers\Admin\Model\Manager;
use Pipe\Db\Entity\Entity;

class Order extends Entity
{
    static public function getFactoryConfig()
    {
        return [
            'table'      => 'orders',
            'properties' => [
                'name'          => [],
                'lang_id'       => [],
                'manager_id'    => [],
                'proposal'      => [],
                'agency'        => [],
                'date_from'     => ['type' => Entity::PROPERTY_TYPE_DATE],
                'date_to'       => ['type' => Entity::PROPERTY_TYPE_DATE],
                'days_count'    => [],
                'comment'       => [],
                'adults'        => [],
                'children'      => [],
                'currency'      => ['default' => Currency::CURRENCY_RUB],
                'errors'        => [],
                'time_create'   => [],
                'time_update'   => [],
                'margin'        => [],
                'outgo'         => [],
                'income'        => [],
                'status'        => [],
                'options'       => ['type' => Entity::PROPERTY_TYPE_JSON],
                'profit'        => [
                    'virtual'   => true,
                    'filters'   => [
						'set'      => function($model) {
							return $model->income - $model->outgo;
						}
					],
                ],
            ],
            'plugins'    => [
                'days' =>  function() {
                    $list = OrderDay::getEntityCollection();
                    $list->select()
                        ->order('date ASC')
                        ->order('time ASC');

                    return $list;
                },
                'clients' =>  function() {
                    $list = OrderClients::getEntityCollection();
                    $list->select()->order('sort');
                    return $list;
                },
                'hotels' =>  function() {
                    $list = OrderHotelsRooms::getEntityCollection();
                    $list->select()->order('sort');
                    return $list;
                },
                'gcalendar' => function() {
                        return new OrderGcalendar();
                },
                'manager' => [
                    'factory' => function() {
                        return new Manager();
                    },
                    'independent' => true,
                ],
                'language' => [
                    'factory' => function($model) {
                        return (new Language())->id($model->get('lang_id'));
                    },
                    'independent' => true,
                ],
            ],
        ];
    }

    public function getUrl() {
        return '/orders/edit/' . $this->id() . '/';
    }

    public function getProposalUrl() {
        return '/orders/proposal/' . $this->id() . '/';
    }
}