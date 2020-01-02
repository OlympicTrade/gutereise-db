<?php
namespace Application\Common\Model;

use Pipe\Db\Entity\Entity;

class Template extends Entity
{

    static public function getFactoryConfig()
    {
        return [
            'table'      => 'template',
            'properties' => [
                'selector'     => [],
                'options'      => ['type' => Entity::PROPERTY_TYPE_JSON],
            ],
        ];
    }

    public function setSelector($selector)
    {
        $selector = strtolower($selector);

        $this->select()->where(['selector' => $selector]);

        if(!$this->load()) {
            $this->set('selector', $selector);
        }

        return $this;
    }
}