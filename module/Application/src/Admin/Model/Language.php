<?php
namespace Application\Admin\Model;

use Pipe\Db\Entity\Entity;

class Language extends Entity
{
    static public function getFactoryConfig()
    {
        return [
            'table'      => 'settings_languages',
            'properties' => [
                'depend'     => [],
                'name'       => [],
                'declension' => ['type' => Entity::PROPERTY_TYPE_JSON],
                'code'       => [],
            ],
        ];
    }

    static function getLanguage($langId)
    {
        return (new self())->id($langId);
    }

    public function setCode($code)
    {
        if(is_string($code) && strlen($code) == 2 && !intval($code)) {
            $this->select()->where(['code' => $code]);
        } else {
            $this->id($code);
        }
        return $this;
    }
}






