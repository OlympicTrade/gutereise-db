<?php
namespace Guides\Admin\Model;

use Pipe\Db\Entity\Entity;

class GuideLanguages extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'guides_languages',
            'properties' => [
                'depend'   => [],
                'lang_id'  => [],
            ],
        ];
    }
}







