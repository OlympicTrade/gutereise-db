<?php
namespace Users\Common\Model;

use Pipe\Db\Entity\Entity;
use Zend\Crypt\Password\Bcrypt;
use Zend\Json\Json;
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
        if(!self::$instance) {
            self::$instance = (new self());
            self::$instance->login();
        }
        return self::$instance;
    }

    static public function getFactoryConfig()
    {
        return [
            'table'      => 'users',
            'properties' => [
                'role_id'       => [],
                'email'         => [],
                'password'      => [
                    'filters' => [
						'set' => function($model, $pass) {
							if(!$pass) {
								$pass = $model->get('password');
							} else {
								$bcrypt = new Bcrypt();
								$pass = $bcrypt->create($pass);
							}
							return $pass;
						}
					],
                ],
                'name'          => [],
                'online'        => [],
                'time_created'  => [],
            ],
            'plugins'    => [
                'role' => function($model) {
                    return new Role(['id' => $model->get('role_id')]);
                },
            ],
        ];
    }

    public function getRoleName()
    {
        return $this->role()->name;
    }

    public function auth($login, $password)
    {
        $this->clear();
        $this->select()->where(['email' => $login]);

        $result = ['status' => false];

        if(!$this->login()) {
            $result['error'] = self::ERROR_LOGIN;
            return $result;
        }

        if(!(new Bcrypt())->verify($password, $this->get('password'))) {
            $result['error'] = self::ERROR_PASSWD;
            return $result;
        }

        $this->getSession()->id = $this->id();

        setcookie(self::COOKIE_NAME, Json::encode([
            'key'   => $this->id(),
            'val' => $this->get('password'),
        ]), time() + (360*24*365), '/');

        $result = ['status' => true];
        return $result;
    }

    public function logout()
    {
        setcookie(self::COOKIE_NAME, null, 0, '/');
        $this->getSession()->id = null;
        $this->clear();
    }

    public function login()
    {
        if($this->isLoaded()) {
            return true;
        }

        $userId = $this->getSession()->id;
        $userId = 2;

        if($this->id($userId)->load()) {
            return true;
        }

        if(!$_COOKIE[self::COOKIE_NAME]) {
            return false;
        }

        try {
            $data = Json::decode($_COOKIE[self::COOKIE_NAME]);
        } catch (\Exception $e) {
            return false;
        }

        $this->clear();
        $this->select()->where([
            'id'        => $data->key,
            'password'  => $data->val,
        ]);

        if($this->load()) {
            return true;
        }

        $this->clear();
        setcookie(self::COOKIE_NAME, null);

        return false;
    }

    protected $rights = [];
    public function getRights()
    {
        if($this->rights)  return $this->rights;

        foreach($this->plugin('role')->plugin('rights') as $row) {
            $this->rights[$row->get('resource')] = $row->get('access');
        }

        return $this->rights;
    }

    /**
     * @return SessionContainer
     */
    protected function getSession()
    {
        if(!$this->session) {
            $this->session = new SessionContainer('users');
        }

        return $this->session;
    }

    public function checkRights($resource, $options = [])
    {
        $options = $options + [
            'redirect' => true,
            'admin'    => false,
            'resource' => $resource,
        ];

        if(strpos($resource, ',')) {
            $result = false;
            foreach (explode(',', $resource) as $tmpRes) {
                $result = $result || $this->checkRights($tmpRes);
            }

            if($result) {
                return true;
            } else {
                return $this->accessDenied($options);
            }
        }

        $resource = strtolower($resource);

        if($options['admin']) {
            $resource = 'admin/' . $resource;
        }

        if(in_array($resource, ['login', 'admin/login'])) {
            return true;
        }

        if(!$this->loaded) {
            $rights = [
                '*'      => 1,
                'admin'  => 0,
            ];
        } else {
            $rights = $this->getRights();
        }

        $res = $resource;

        while($res) {
            if(array_key_exists($res, $rights)) {
                if($rights[$res]) {
                    return true;
                } else {
                    return $this->accessDenied($options);
                }
            }

            if(!strripos($res, '/')) {
                if(array_key_exists('*', $rights)) {
                    return true;
                }

                return $this->accessDenied($options);
            }

            $res = substr($res, 0, strripos($res, '/'));
        }

        return $this->accessDenied($options);
    }

    protected function accessDenied($options)
    {
        if(!$options['redirect']) {
            return false;
        }

        if($options['admin']) {
            if($this->loaded) {
                header('location: /pipe/access-denied/');
            } else {
                header('location: /pipe/login/');
            }
        } else {
            if($this->loaded) {
                header('location: /access-denied/');
            } else {
                header('location: /login/');
            }
        }
        die();
    }
}