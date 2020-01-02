<?php
namespace Application\Admin\Model;

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
            'parent'      => \Application\Common\Model\Settings::class,
        ];
    }
}