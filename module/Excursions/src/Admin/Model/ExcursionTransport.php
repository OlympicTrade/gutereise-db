<?php
namespace Excursions\Admin\Model;

use Pipe\Db\Entity\Entity;

class ExcursionTransport extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'excursions_days_transport',
            'properties' => [
                'depend'        => [],
                'type'          => [],
                'transport_id'  => [],
                'duration'      => [
                    'filterOut' => function($model, $val) {
                        return $val ? $val : '';
                    }
                ],
                'sort'          => [],
            ],
        ];
    }
}







