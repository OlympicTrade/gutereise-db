<?php
namespace Hotels\Admin\Model;

use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\Traits\Admin\Profile;
use Translator\Admin\Model\Translator;

class Hotel extends Entity
{
    use Profile;

    const BREAKFAST_BUFFET      = 1;
    const BREAKFAST_CONTINENTAL = 2;
    const BREAKFAST_NO          = 3;

    static public function getFactoryConfig()
    {
        return [
            'table'      => 'hotels',
            'properties' => [
                'name'            => [],
                'contacts'        => ['type' => Entity::PROPERTY_TYPE_JSON],
                'breakfast'       => ['type' => Entity::PROPERTY_TYPE_JSON],
                'company_details' => ['type' => Entity::PROPERTY_TYPE_JSON],
                'comment'         => [],
            ],
            'plugins' => [
                'rooms' => function($model) {
                    $list = HotelRoom::getEntityCollection();
                    $list->select()->order('capacity ASC');
                    return $list;
                },
            ],
        ];
    }

    public function init($options)
    {
        Translator::setModelEvents($this, ['include' => ['name'],
            'plugins' => [
                'rooms' => ['include' => ['name']]
            ]
        ]);
    }

    public function getBreakfastOpts()
    {
        $opts = [];

        $bf = 1; //buffet is free
        $breakfast = $this->get('breakfast');
        if($breakfast->buffet->active) {
            if(!$breakfast->buffet->price) {
                $opts[self::BREAKFAST_BUFFET] = 'Шведский стол - бесплатно';
            } else {
                $bf = 0;
                $opts[self::BREAKFAST_BUFFET] = 'Шведский стол - ' . $breakfast->buffet->price . ' руб.';
            }
        }

        $ct = 1; //continental is free
        if($breakfast->continental->active) {
            if(!$breakfast->continental->price) {
                $opts[self::BREAKFAST_CONTINENTAL] = 'Континентальный завтрак - бесплатно';
            } else {
                $ct = 0;
                $opts[self::BREAKFAST_CONTINENTAL] = 'Континентальный завтрак - ' . $breakfast->continental->price . ' руб.';
            }
        }

        if(!$bf && $ct) {
            $opts = array_reverse($opts);
        }

        if(!$opts || (!$bf && !$ct)) {
            $opts = array_merge([self::BREAKFAST_NO => 'Нет'], $opts);
        }

        return $opts;
    }

    public function getUrl()
    {
        return '/hotels/edit/' . $this->id() . '/';
    }
}