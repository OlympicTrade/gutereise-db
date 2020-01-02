<?php
namespace Sync\Model;

use Pipe\Db\Entity\Entity;

class Sync extends Entity
{

    public function __construct()
    {
        $this->setTable('sync');

        $this->addProperties(array(
            'name'          => array(),
            'time_create'   => array(),
        ));
    }
}