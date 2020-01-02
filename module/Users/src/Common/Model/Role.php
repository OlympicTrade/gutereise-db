<?php
namespace Users\Common\Model;

use Pipe\Db\Entity\Entity;

class Role extends Entity
{
    static public function getFactoryConfig()
    {
        return [
            'table'      => 'users_roles',
            'properties' => [
                'name'          => [],
            ],
            'plugins'    => [
                'rights' => function() {
                    return RoleRights::getEntityCollection();
                },
            ],
        ];
    }
}







