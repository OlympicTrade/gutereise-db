<?php
namespace Users\Admin\Model;

use Pipe\Db\Entity\Entity;

class Role extends Entity
{

    static public function getFactoryConfig()
    {
        return [
            'parent'     => \Users\Common\Model\Role::class,
            'plugins'    => [
                'rights' => function() {
                    return RoleRights::getEntityCollection();
                },
            ],
        ];
    }
    /*public function __construct($options = [])
    {
        parent::__construct($options);

        $this->setTable('users_roles');

        $this->addProperties([
            'name'        => [],
        ]);

        $this->addPlugin('rights', function($model) {
            $list = RoleRights::getEntityCollection();
            return $list;
        });
    }*/
}







