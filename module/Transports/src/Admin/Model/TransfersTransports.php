<?php
namespace Transports\Admin\Model;

use Pipe\Db\Entity\Entity;
use Drivers\Admin\Model\Driver;

class TransfersTransports extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'transports_transfers_transports',
            'properties' => [
                'depend'        => [],
                'transport_id'  => [],
                'income'        => [],
                'outgo'         => [],
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
}