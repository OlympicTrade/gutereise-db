<?php
namespace Translator\Admin\Model;

use Pipe\Db\Entity\Entity;

class Translate extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'translator',
            'properties' => [
                'code'  => [],
                'url'   => [],
                'ru'    => [],
                'en'    => [],
                'de'    => [],
            ],
            'events' => [
                [
                    'event'     => [Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE],
                    'function'  => function ($model) {
                        $model->updateFromDuplicates();
                        return true;
                    }
                ],
                [
                    'event'     => [Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE],
                    'function'  => function ($model) {
                        $model->set('code', Translator::getCode($model->get('ru')));
                        return true;
                    }
                ],
            ]
        ];
    }

    /*public function __construct($options = [])
    {
        parent::__construct($options);

        $this->setTable('translator');

        $this->addProperties([
            'code'  => [],
            'url'   => [],
            'ru'    => [],
            'en'    => [],
            'de'    => [],
        ]);

        $this->getEventManager()->attach([Entity::EVENT_POST_INSERT], function ($event) {
            $event->getTarget()->updateFromDuplicates();
            return true;
        });

        $this->getEventManager()->attach([Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE], function ($event) {
            $model = $event->getTarget();
            $model->set('code', Translator::getCode($model->get('ru')));
            return true;
        });
    }*/

    public function updateFromDuplicates() {
        $translate = new self();
        $translate->select()
            ->where
            ->equalTo('code', $this->get('code'))
            ->notEqualTo('id', $this->id());

        if($translate->load()) {
            $this->setVariables([
                'en'    => $translate->get('en'),
                'de'    => $translate->get('de'),
            ]);
        }
    }
}