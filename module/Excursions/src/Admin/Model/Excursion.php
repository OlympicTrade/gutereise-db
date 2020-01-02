<?php
namespace Excursions\Admin\Model;

use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\EntityCollection;
use Translator\Admin\Model\Translator;

class Excursion extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'excursions',
            'properties' => [
                'name'    => [],
                'hits'    => [],
                //'days'    => [],
            ],
            'plugins'    => [
                'days' => function($model) {
                    return EntityCollection::factory(ExcursionDay::class, ['sort' => 'sort ASC']);
                },
                'margin' => function($model) {
                    return EntityCollection::factory(ExcursionMargin::class);
                },
            ],
            'events' => [
                [
                    'events'   => [Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE],
                    'function' => function ($event) {
                        $model = $event->getTarget();
                        $model->set('days', $model->plugin('days')->count());
                        return true;
                    }
                ],
            ],
        ];
    }

    protected function init($options)
    {
        parent::init($options);

        Translator::setModelEvents($this, ['exclude' => 'all',
            'plugins' => [
                'days' => ['exclude' => 'all', 'plugins' => [
                    //'options'   => [],
                    'timetable' => ['include' => ['name']],
                    'extra'     => ['include' => ['proposal_name']],
                ]],
            ]
        ]);
    }

    /*public function __construct($options = [])
    {
        parent::__construct($options);

        $this->setTable('excursions');

        $this->addProperties([
            'name'    => [],
            'hits'    => [],
            'days'    => [],
        ]);

        $this->addPlugin('days', function($model) {
            $list = ExcursionDay::getEntityCollection();
            $list->select()->order('sort ASC');
            return $list;
        });

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            $model->set('days', $model->plugin('days')->count());

            return true;
        });

        $this->addPlugin('margin', function($model) {
            $margin = ExcursionMargin::getEntityCollection();
            return $margin;
        });

        Translator::setModelEvents($this, ['exclude' => 'all',
            'plugins' => [
                'days' => ['exclude' => 'all', 'plugins' => [
                    'attrs'     => [],
                    'timetable' => ['include' => ['name']],
                    'extra'     => ['include' => ['proposal_name']],
                ]],
            ]
        ]);
    }*/

    public function getUrl()
    {
        return '/excursions/edit/' . $this->id() . '/';
    }
}






