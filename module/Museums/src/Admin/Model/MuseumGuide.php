<?php
namespace Museums\Admin\Model;

use Pipe\Db\Entity\Entity;

class MuseumGuide extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'museum_guides',
            'properties' => [
                'depend'     => [],
                'foreigners' => [],
                'age'        => [],
                'count'      => [],
                'price'      => [],
            ]
        ];
    }

    /*public function __construct()
    {
        $this->setTable('museum_guides');

        $this->addProperties([
            'depend'     => [],
            'foreigners' => [],
            'age'        => [],
            'count'      => [],
            'price'      => [],
        ]);
    }*/
}







