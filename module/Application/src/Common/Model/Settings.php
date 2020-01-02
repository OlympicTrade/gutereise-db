<?php
namespace Application\Common\Model;

use Application\Admin\Model\Language;
use Pipe\Db\Entity\Traits\Admin\Profile;
use Pipe\Db\Entity\Entity;

class Settings extends Entity
{
    use Profile;

    static protected $instance;
    static public function getInstance()
    {
        return self::$instance ?? self::$instance = (new self())->id(1)->load();
    }

    static public function getFactoryConfig()
    {
        return [
            'table'      => 'settings',
            'properties' => [
                'template'          => ['type' => Entity::PROPERTY_TYPE_JSON],
                'data'              => ['type' => Entity::PROPERTY_TYPE_JSON],
                'margin'            => ['type' => Entity::PROPERTY_TYPE_JSON],
                'currency'          => ['type' => Entity::PROPERTY_TYPE_JSON],
                'contacts'          => ['type' => Entity::PROPERTY_TYPE_JSON],
                'company_details'   => ['type' => Entity::PROPERTY_TYPE_JSON],
            ],
            'plugins' => [
                'languages' => function($model) {
                    return Language::getEntityCollection();
                }
            ]
        ];
    }

    static public function getLanguages()
    {
        return self::getInstance()->plugin('languages');
    }
}