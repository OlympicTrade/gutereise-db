<?php
namespace Clients\Admin\Model;

use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\Traits\Admin\Profile;

class Client extends Entity
{
    use Profile;

    static public function getFactoryConfig() {
        return [
            'table'      => 'clients',
            'properties' => [
                'name'            => [],
                'comment'         => [],
                'contacts'        => ['type' => Entity::PROPERTY_TYPE_JSON],
                'company_details' => ['type' => Entity::PROPERTY_TYPE_JSON],
            ],
            'plugins' => [
                'employees' => function() {
                    return ClientEmployees::getEntityCollection();
                },
            ],
        ];
    }
}