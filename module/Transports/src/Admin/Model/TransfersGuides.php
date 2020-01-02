<?php
namespace Transports\Admin\Model;

use Pipe\Db\Entity\Entity;

class TransfersGuides extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'transports_transfers_guides',
            'properties' => [
                'depend'     => [],
                'lang_id'    => [],
                'income'     => [],
                'outgo'      => [],
            ],
            'plugins'    => [
                'transfer' => [
                    'factory' => function($model){
                        return new Transfer(['id' => $model->get('depend')]);
                    },
                    'independent' => true,
                ],
            ],
        ];
    }

    /*public function __construct()
    {
        $this->setTable('transports_transfers_guides');
        $this->addProperties([
            'depend'     => [],
            'lang_id'    => [],
            'income'     => [],
            'outgo'      => [],
        ]);

        $this->addPlugin('transfer', function($model) {
            $item = new Transfer();
            $item->select()->where(['id' => $model->get('depend')]);
            return $item;
        }, ['independent' => true]);
    }*/
}