<?php
namespace Excursions\Admin\Model;

use Application\Admin\Model\Nationality;
use Pipe\DateTime\Time;
use Pipe\Db\Entity\Entity;
use Excursions\Admin\Form\ExcursionsDayEditForm;
use Pipe\Db\Entity\EntityCollection;
use Transports\Admin\Model\Transfer;

class ExcursionDay extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'excursions_days',
            'properties' => [
                'depend'            => [],
                'transfer_time'     => ['default' => '00:30:00'],
                'car_delivery_time' => ['default' => '01:00:00'],
                'min_time'          => ['default' => '10:00:00'],
                'max_time'          => ['default' => '16:00:00'],
                'transfer_id'       => [],
                'options'           => ['type' => Entity::PROPERTY_TYPE_JSON],
                'sort'              => [],
            ],
            'plugins'    => [
                'transfer' => [
                    'factory' => function($model){
                        return new Transfer(['id' => $model->get('transfer_id')]);
                    },
                    'independent' => true,
                ],
                'excursion' => [
                    'factory' => function($model){
                        return new Excursion(['id' => $model->get('depend')]);
                    },
                    'independent' => true,
                ],
                'extra' => function($model, $options){
                    $extra = EntityCollection::factory(ExcursionExtra::class);

                    $extra->select()->order('sort');

                    if($options['foreigners']) {
                        $extra->select()->where
                            ->equalTo('depend', $model->id())
                            ->in('foreigners', $options['foreigners']);
                    }

                    if($options['tourists']) {
                        $extra->select()->where
                            ->nest()
                            ->lessThanOrEqualTo('tourists_from', $options['tourists'])
                            ->greaterThanOrEqualTo('tourists_to', $options['tourists'])
                            ->unnest();
                    }

                    return $extra;
                },
                'museums' => function($model){
                    return EntityCollection::factory(ExcursionMuseums::class, ['sort' => 'sort']);
                },
                'transport' => function($model, $options) {
                    return EntityCollection::factory(ExcursionTransport::class, ['sort' => 'sort']);
                },
                'guides' => function($model, $options) {
                    $guides = EntityCollection::factory(ExcursionGuides::class);
                    $guides->setParent($model);
                    $guides->select()->order('sort');

                    if($options['foreigners']) {
                        $guides->select()->where
                            ->equalTo('depend', $model->id())
                            ->in('foreigners', $options['foreigners']);
                    }

                    if($options['tourists']) {
                        $guides->select()
                            ->order('tourists DESC')
                            ->where
                            ->lessThanOrEqualTo('tourists', $options['tourists']);
                    }

                    return $guides;
                },
                'timetable' => function($model, $options) {
                    $timetable = EntityCollection::factory(ExcursionTimetable::class);
                    $timetable->select()->order('sort');

                    if($options['foreigners']) {
                        $timetable->select()->where
                            ->equalTo('depend', $model->id())
                            ->in('foreigners', $options['foreigners']);
                    }

                    if($options['tourists']) {
                        $timetable->select()
                            ->where
                            ->lessThanOrEqualTo('tourists_from', $options['tourists'])
                            ->greaterThanOrEqualTo('tourists_to', $options['tourists']);
                    }

                    return $timetable;
                },
            ],
        ];
    }

    public function getExcursion()
    {
        $excursion = new Excursion();
        $excursion->id($this->get('depend'));

        return $excursion;
    }
    public function getForm()
    {
        $form = new ExcursionsDayEditForm();
        $form->setOptions(['model' => $this]);
        $form->init();
        $form->setDataFromModel();

        return $form;
    }

    public function getDuration($options = [])
    {
        $options = $options + [
            'tourists'   => 10,
            'foreigners' => [Nationality::NATIONALITY_RUSSIAN, Nationality::NATIONALITY_ALL],
        ];

        $duration = Time::getDT();
        foreach ($this->plugin('timetable', $options) as $row) {
            $duration->addition($row->get('duration'));
        }

        return $duration;
    }
}