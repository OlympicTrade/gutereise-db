<?php
namespace Guides\Admin\Model;

use Pipe\Db\Entity\Entity;

use Excursions\Admin\Model\Excursion;

class GuideExcursions extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'guides_excursions',
            'properties' => [
                'depend'        => [],
                'excursion_id'  => [],
            ],
            'plugins'    => [
                'excursion' => [
                    'factory' => function($model){
                        return new Excursion(['id' => $model->get('excursion_id')]);
                    },
                    'independent' => true,
                ],
                'guide' => [
                    'factory' => function($model){
                        return new Guide(['id' => $model->get('guide_id')]);
                    },
                    'independent' => true,
                ],
            ],
        ];
    }

    /*public function __construct()
    {
        $this->setTable('guides_excursions');

        $this->addProperties(array(
            'depend'        => array(),
            'excursion_id'  => array(),
        ));

        $this->addPlugin('excursion', function($model) {
            $excursion = new Excursion();
            $excursion->setId($model->get('excursion_id'));

            return $excursion;
        }, array('type' => 'parent'));

        $this->addPlugin('guide', function($model) {
            $guide = new Guide();
            $guide->setId($model->get('guide_id'));

            return $guide;
        }, array('type' => 'parent'));
    }*/
}







