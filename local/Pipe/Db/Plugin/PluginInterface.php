<?php
namespace Pipe\Db\Plugin;

use Pipe\Db\Entity\Entity;

interface PluginInterface
{
    /**
     * @param Entity $parent
     * @return mixed
     */
    public function setParent(Entity $parent);
}