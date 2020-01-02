<?php
namespace Managers\Admin\Model;

use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\Traits\Admin\Profile;
use Translator\Admin\Model\Translator;

class Manager extends Entity
{
    use Profile;

    static public function getFactoryConfig() {
        return [
            'table'      => 'managers',
            'properties' => [
                'name'      => [],
                'comment'   => [],
                'contacts'  => ['type' => Entity::PROPERTY_TYPE_JSON],
            ],
        ];
    }

    public function init($options)
    {
        Translator::setModelEvents($this, ['include' => ['name']]);
    }

    public function getUrl()
    {
        return '/managers/edit/' . $this->id() . '/';
    }
}







