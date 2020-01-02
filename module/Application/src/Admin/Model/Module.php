<?php
namespace Application\Admin\Model;

use Pipe\Db\Entity\EntityHierarchy;

class Module extends EntityHierarchy
{
    static public function getFactoryConfig()
    {
        return [
            'parent' => \Application\Common\Model\Module::class,
        ];
    }
}






