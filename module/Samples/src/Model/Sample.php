<?php
namespace Samples\Model;

use function foo\func;
use Pipe\Db\Entity\Entity;

class Sample extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'samples',
            'parent'     => Sample::class,
            'properties' => [
                'depend'     => [],
                'name'       => [],
                'test'       => [
                    'filters' => [
                        'set' => function($model, $val) {
                            return strtolower($value);
                        },
                        'get' => function($model, $val) {
                            return strtolower($value);
                        }
                    ],
                ],
            ],
            'plugins'    => [
                'plugin1' => function($model) {
                    return EntityCollection::factory(Sample::class);
                },
                'plugin2' => [
                    'factory' => function($model){
                        return new Sample(['id' => $model->get('sample_id')]);
                    },
                    'independent' => true,
                ],
            ],
            'events' => [
                [
                    'events'   => [Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE],
                    'function' => function ($event) {
                        $model = $event->getTarget();
                    }
                ]
            ],
        ];
    }
}
