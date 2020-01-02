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
                'depend' => [],
                'name'   => [],
                'code'   => [],
            ],
        ];
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






