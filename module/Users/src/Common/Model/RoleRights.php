<?php
namespace Users\Common\Model;

use Pipe\Db\Entity\Entity;

class RoleRights extends Entity
{
    static public function getFactoryConfig()
    {
        return [
            'table'      => 'users_roles_rights',
            'properties' => [
                'depend'   => [],
                'resource' => [],
                'access'   => [],
            ],
        ];
    }
}