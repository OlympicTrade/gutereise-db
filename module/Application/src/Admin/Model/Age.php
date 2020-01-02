<?php
namespace Application\Admin\Model;

use Pipe\Db\Entity\Entity;

class Age
{
    const AGE_CHILDREN  = 1;
    const AGE_ADULT     = 2;
    const AGE_ALL       = 3;

    static public $ageType = [
        self::AGE_ALL       => 'Любой',
        self::AGE_CHILDREN  => 'Дети',
        self::AGE_ADULT     => 'Взрослые',
    ];
}