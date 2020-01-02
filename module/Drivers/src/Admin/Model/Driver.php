<?php
namespace Drivers\Admin\Model;

use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\Traits\Admin\Profile;
use Translator\Admin\Model\Translator;

class Driver extends Entity
{
    use Profile;

    static public function getFactoryConfig() {
        return [
            'table'      => 'drivers',
            'properties' => [
                'name'      => [],
                'contacts'  => ['type' => Entity::PROPERTY_TYPE_JSON],
                'capacity'  => [],
                'comment'   => [],
            ],
        ];
    }

    protected function init($options)
    {
        Translator::setModelEvents($this, ['include' => ['name']]);
    }

    public function getUrl()
    {
        return '/drivers/edit/' . $this->id() . '/';
    }
}