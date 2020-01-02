<?php
namespace Pipe\Db\Entity\Containers;

class Num extends AbstractContainer
{
    public function set($value)
    {
        return parent::set((int) $value);
    }
}