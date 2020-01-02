<?php
namespace Clients\Admin\Model;

use Pipe\Db\Entity\Entity;

class ClientEmployees extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'clients_employees',
            'properties' => [
                'depend'        => [],
                'name'          => [],
                'job'           => [],
                'phone_1'       => [],
                'phone_2'       => [],
                'phone_3'       => [],
                'email'         => [],
            ],
        ];
    }
}







