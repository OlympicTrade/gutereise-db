<?php
namespace Guides\Admin\Model;

use Pipe\Db\Entity\Entity;

class GuideMuseums extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'guides_museums',
            'properties' => [
                'depend'     => [],
                'museum_id'  => [],
            ],
        ];
    }
}







