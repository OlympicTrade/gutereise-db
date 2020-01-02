<?php
namespace Users\Admin\Model;

use Pipe\Db\Entity\Entity;

class RoleRights extends Entity
{
    static public function getFactoryConfig()
    {
        return [
            'parent'     => \Users\Common\Model\RoleRights::class,
        ];
    }
}