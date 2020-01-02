<?php
namespace Users\Admin\Model;

use Pipe\Db\Entity\Entity;
use Zend\Session\Container as SessionContainer;

class User extends Entity
{
    const ERROR_LOGIN  = 1;
    const ERROR_PASSWD = 2;

    const COOKIE_NAME  = 'profile';

    static public $errorsText = [
        self::ERROR_LOGIN  => 'Неверный логин',
        self::ERROR_PASSWD => 'Неверный пароль',
    ];

    /**
     * @var SessionContainer
     */
    protected $session;

    /** @var $this */
    static protected $instance;
    static public function getInstance() {
        return self::$instance ?? self::$instance = (new self())->login();
    }

    static public function getFactoryConfig()
    {
        return [
            'parent'     => \Users\Common\Model\User::class,
            'plugins'    => [
                'role' => [
                    'factory' => function($model) {
                        $role = new Role();
                        $role->select()->where(['id' => $model->get('role_id')]);
                        return $role;
                    },
                    'independent' => true,
                ],
            ],
        ];
    }
}