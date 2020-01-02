<?php
namespace Application\Admin\Model;

use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\EntityHierarchy;

class Menu extends EntityHierarchy
{
    static public function getFactoryConfig()
    {
        return [
            'table'      => 'menu',
            'properties' => [
                'parent'            => [],
                'name'              => [],
                'icon'              => [],
                'url'               => ['type' => Entity::PROPERTY_TYPE_JSON],
                'access'            => ['type' => Entity::PROPERTY_TYPE_JSON],
                'options'           => ['type' => Entity::PROPERTY_TYPE_JSON],
                'sort'              => [],
            ],
        ];
    }
}