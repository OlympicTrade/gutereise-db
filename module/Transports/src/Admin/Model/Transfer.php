<?php
namespace Transports\Admin\Model;

use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\EntityCollection;

class Transfer extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'transports_transfers',
            'properties' => [
                'name'        => [],
                'duration'    => [],
            ],
            'plugins'    => [
                'transport' => [
                    'factory' => function($model){
                        return EntityCollection::factory(TransfersTransports::class);
                    },
                    'options' => [
                        'independent' => true,
                    ],
                ],
                'guides' => [
                    'factory' => function($model){
                        return EntityCollection::factory(TransfersGuides::class);
                    },
                    'options' => [
                        'independent' => true,
                    ],
                ],
            ],
        ];
    }

    /*public function __construct($options = [])
    {
        parent::__construct($options);

        $this->setTable('transports_transfers');

        $this->addProperties([
            'name'        => [],
            'duration'    => [],
        ]);

        $this->addPlugin('transport', function($model) {
            $item = TransfersTransports::getEntityCollection();
            $item->select()->where(['depend' => $model->id()]);
            return $item;
        });

        $this->addPlugin('guides', function($model) {
            $item = TransfersGuides::getEntityCollection();
            $item->select()->where(['depend' => $model->id()]);
            return $item;
        });
    }*/

    public function getUrl()
    {
        return '/transports/transfers/' . $this->id() . '/';
    }
}







