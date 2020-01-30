<?php
/**
 * Example
 * //Create entity and set properties
 * $entity = new Entity();
 * $entity->addProperties([
 *    ['name' => 'name'],
 *    ['name' => 'role'],
 * ]);
 *
 * $entity->name = 'roman';
 * $entity->role = 'master';
 * $entity->save();
 * $entity->remove();
 */

namespace Pipe\Db\Entity;

//use Pipe\Cache\CacheAwareInterface;
use Pipe\Db\AbstractDb;
use \Pipe\Db\Entity\Containers;

use Pipe\Db\Plugin\PluginInterface;
use PhpOffice\PhpWord\Exception\Exception;
use Pipe\Form\Element\EntityAware;
use Zend\Db\Adapter\AdapterAwareInterface;

use ArrayAccess;
use Iterator;
use Serializable;
use Zend\Json\Json;
use Zend\Stdlib\ArrayObject;

class Entity extends AbstractDb implements ArrayAccess, Iterator, Serializable, PluginInterface
{
    const PROPERTY_TYPE_TEXT = 'text';
    const PROPERTY_TYPE_NUM  = 'num';
    const PROPERTY_TYPE_JSON = 'json';
    const PROPERTY_TYPE_DATE = 'date';
    const PROPERTY_TYPE_TIME = 'time';

    const EVENT_PRE_UPDATE  = 'update.pre';
    const EVENT_POST_UPDATE = 'update.post';
    const EVENT_PRE_INSERT  = 'insert.pre';
    const EVENT_POST_INSERT = 'insert.post';
    const EVENT_PRE_DELETE  = 'delete.pre';
    const EVENT_POST_DELETE = 'delete.post';
    const EVENT_PRE_LOAD    = 'load.pre';
    const EVENT_POST_LOAD   = 'load.post';

    /** @var bool */
    protected $loaded = false;

    /** @var int */
    protected $id = 0;

    /** @var array */
    public $properties;

    /** @var array */
    protected $plugins;

    /*
    Example constructor
    static public function getFactoryConfig() {
        return [
            'table'      => 'samples',
            'parent'     => Sample::class,
            'properties' => [
                'depend'     => [],
                'name'       => [],
                'test'       => [
                    'filters' => [
                        'set' => function($model, $val) {
                            return strtolower($value);
                        },
                        'get' => function($model, $val) {
                            return strtolower($value);
                        }
                    ],
                ],
            ],
            'plugins'    => [
                'plugin1' => function($model) {
                    return EntityCollection::factory(Sample::class);
                },
                'plugin2' => [
                    'factory' => function($model){
                        return new Sample(['id' => $model->get('sample_id')]);
                    },
                    'independent' => true,
                ],
            ],
            'events' => [
                [
                    'events'   => [Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE],
                    'function' => function ($event) {
                        $model = $event->getTarget();
                    }
                ]
            ],
        ];
    }
    */

    /**
     * Entity constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (isset($options['id'])) {
            $this->id($options['id']);
            unset($options['id']);
        }

        $this->init($options);
    }

    protected function init($options)
    {
        $this->select()->where($options);
    }

    public function table()
    {
        return $this->table ?? $this->table = $this->getConfig()->get('table');
    }

    /**
     * @param $name
     * @param array $options
     * @param bool $forced
     * @return Entity|bool
     * @throws \Exception
     */
    public function plugin($name, $options = [], $forced = false)
    {
        $this->load();

        if(!empty($this->plugins[$name]['object']) && !$forced) {
            return $this->plugins[$name]['object'];
        }

        if(!$pluginFactory = $this->getConfig()->get('plugins')[$name]) {
            throw new \Exception('Plugin "' . $name . '" not found');
        }

        $options = $options + [
            'independent' => false,
            'serialize'   => true,
        ];

        if(is_callable($pluginFactory)) {
            $obj = call_user_func_array($pluginFactory, [$this, $options]);
        } elseif(is_string($pluginFactory)) {
            $obj = new $pluginFactory();
        } elseif(is_array($pluginFactory)) {
            $obj = call_user_func_array($pluginFactory['factory'], [$this, $options]);
        } else {
            throw new \Exception('Unknown plugin type');
        }

        $plugin = $this->addPlugin($name, $obj, $options);

        return $plugin;
    }

    public function addPlugin($name, $obj, $options = [])
    {
        if (array_key_exists($name, $this->plugins)) {
            return false;
        }

        $independent = $options['independent'] ?? false;

        $this->plugins[$name] = [
            'object'      => $obj,
            'independent' => $independent
        ];

        $plugin = &$this->plugins[$name]['object'];

        if($plugin instanceof AdapterAwareInterface) {
            $plugin->setDbAdapter($this->getDbAdapter());
        }

        /*if($this->cache && $plugin instanceof CacheAwareInterface) {
            $plugin->setCacheAdapter($this->cache);
        }*/

        if($plugin instanceof PluginInterface) {
            $plugin->setParent($this);
        }

        return $plugin;
    }

    public function __call($method, $params)
    {
        return $this->plugin($method);
    }

    public function clearPlugin($name)
    {
        $this->plugins[$name]['object'] = null;
    }

    /**
     * @return EntityCollection
     */
    public function getCollection()
    {
        $collection = new EntityCollection();
        $collection->setPrototype($this->getClearCopy());

        return $collection;
    }

    /**
     * @param null $id
     * @return $this|int
     */
    public function id($id = null)
    {
        if($id === null) {
            return $this->id;
        }

        if($id == 0) {
            $this->loaded = false;
        }

        $this->id = (int) $id;

        return $this;
    }

    /**
     * @return bool
     */
    /*public function isSaved()
    {
        return $this->saved;
    }*/

    /**
     * @param bool $transaction
     * @return bool
     * @throws \Exception
     */
    public function save($transaction = true)
    {
        $transaction = $transaction && $this->transaction;

        if ($transaction) {
            $this->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
        }
        $isUpdate = $this->id;

        $trigger = $isUpdate ? self::EVENT_PRE_UPDATE : self::EVENT_PRE_INSERT;
        $events = $this->getEventManager()->trigger($trigger, $this);
        $commit = !($events->count() && $events->contains(false));

        /*if ($isUpdate) {
            $commit = !$this->getEventManager()->trigger(self::EVENT_PRE_UPDATE, $this)->contains(false);
        } else {
            $commit = !$this->getEventManager()->trigger(self::EVENT_PRE_INSERT, $this)->contains(false);
        }*/

        $data = [];

        if($isUpdate) {
            foreach ($this->getProperties() as $name => $propOpts) {
                /** @var Containers\AbstractContainer $container */
                $container = $propOpts['container'];

                if ($propOpts['virtual'] || !$container->isChanged()) {
                    continue;
                }

                $container->isChanged(false);
                $data[$name] = $container->serialize();
            }
        } else {
            foreach ($this->getProperties() as $name => $propOpts) {
                if ($propOpts['virtual']) {
                    continue;
                }

                $container = $propOpts['container'];
                $data[$name] = $container->serialize();
            }
        }

        if (!$commit && $transaction) {
            $this->getDbAdapter()->getDriver()->getConnection()->rollback();
            return false;
        }

        if (!empty($data)) {
            if ($isUpdate) {
                $update = $this->update();
                $update->where([$this->primary => $this->id()]);
                $update->set($data);
                $this->execute($update);
            } else {
                if ($this->getParent() && $this->hasProperty('depend')) {
                    $data['depend'] = $this->getParent()->id();
                }

                $insert = $this->insert();
                $insert->values($data);
                $this->execute($insert);
                $this->id = $this->getDbAdapter()->getDriver()->getLastGeneratedValue();
            }
        } elseif (!$this->id) {
            $sql = 'INSERT INTO ' . $this->table() . ' VALUES ();';
            $this->getDbAdapter()->getDriver()->getConnection()->execute($sql);
            $this->id = $this->getDbAdapter()->getDriver()->getLastGeneratedValue();
        }

        foreach ($this->plugins as $plugin) {
            if (empty($plugin['object']) || $plugin['independent']) {
                continue;
            }

            $commit = $plugin['object']->save(false);

            if ($transaction && !$commit) {
                $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                return false;
            }
        }

        $trigger = $isUpdate ? self::EVENT_POST_UPDATE : self::EVENT_POST_INSERT;
        $events = $this->getEventManager()->trigger($trigger, $this);
        $commit = !($events->count() && $events->contains(false));

        /*if ($isUpdate) {
            $commit = !$this->getEventManager()->trigger(self::EVENT_POST_UPDATE, $this)->contains(false);
        } else {
            $commit = !$this->getEventManager()->trigger(self::EVENT_POST_INSERT, $this)->contains(false);
        }*/

        if ($transaction) {
            if (!$commit) {
                $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                return false;
            } else {
                $this->getDbAdapter()->getDriver()->getConnection()->commit();
            }
        }

        $this->loaded = true;
        //$this->saved = true;

        //$this->cacheClear();

        return $this;
    }

    /**
     * @param bool $forced
     * @return $this|bool
     * @throws Exception
     */
    public function load($forced = false)
    {
        if ($this->loaded) {
            return $this;
        }

        /*if ($this->cacheLoad()) {
            $this->saved = true;
            return $this;
        }*/

        $this->getEventManager()->trigger(self::EVENT_PRE_LOAD, $this);

        $select = $this->getLoadSelect($forced);
        if (!$select) {
            return false;
        }

        $select->limit(1);

        $result = $this->execute($select)->current();

        if (empty($result)) {
            return false;
        }

        $this->fill($result);

        $this->id($result[$this->primary]);

        //$this->saved = true;

        //$this->cacheSave();

        $this->getEventManager()->trigger(self::EVENT_POST_LOAD, $this);

        return $this;
    }

    /**
     * @param bool $forced
     * @return bool|\Zend\Db\Sql\Select
     * @throws Exception
     */
    protected function getLoadSelect($forced = false)
    {
        $select = clone $this->select();

        if ($this->id()) {
            $select->where(['t.' . $this->primary => $this->id]);
        } elseif (!$forced && !$select->where->getPredicates() && !$this->getParent()) {
            return false;
        }

        if ($this->getParent() && $this->hasProperty('depend')) {
            $select->where(['depend' => $this->getParent()->id()]);
        }

        return $select;
    }

    /**
     * @param bool $transaction
     * @return bool
     * @throws \Exception
     */
    public function remove($transaction = true)
    {
        /*if(!$this->id) {
            return false;
        }*/

        $this->load();

        $transaction = $transaction && $this->transaction;

        if($transaction) {
            $this->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
        }

        $events = $this->getEventManager()->trigger(self::EVENT_PRE_DELETE, $this);

        if ($events->count() && $events->contains(false)) {
            if($transaction) {
                $this->getDbAdapter()->getDriver()->getConnection()->rollback();
            }
            return false;
        }

        foreach($this->plugins as $name => $options) {
            if(empty($options['object'])) {
                continue;
            }

            $plugin = $this->plugin($name);

            if($options['independent']) {
                $plugin->clear();
                continue;
            }

            $plugin = $this->plugin($name);

            $commit = $plugin->remove(false);

            if($transaction && !$commit) {
                $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                return false;
            }
        }

        $delete = $this->delete();
        $delete->where([
            $this->primary => $this->id,
        ]);

        $this->execute($delete);

        foreach($this->getProperties() as $property) {
            $property['container']->clear()->isChanged(false);
        }

        $this->id = 0;


        $events = $this->getEventManager()->trigger(self::EVENT_POST_DELETE, $this);
        $commit = !($events->count() && $events->contains(false));

        if ($transaction) {
            if ($commit) {
                $this->getDbAdapter()->getDriver()->getConnection()->commit();
            } else {
                $this->getDbAdapter()->getDriver()->getConnection()->rollback();
            }
        }

        //$this->cacheClear();

        return $commit;
    }

    public function clear()
    {
        foreach($this->getProperties() as $key => $property) {
            $property['container']->clear()->isChanged(false);
        }

        $this->plugins = [];
        $this->clearSelect();

        $this->id(0);
        //$this->loaded = false;
        //$this->saved = false;

        return $this;
    }

    static public function getFactoryConfig()
    {
        return [];
    }

    /** @var Config */
    protected $config;
    protected function getConfig()
    {
        if($this->config === null) {
            return $config = ConfigCollector::getInstance()->getConfig(get_called_class());
        } else {
            return $this->config;
        }
    }

    public function getProperty($name) {
        return $this->getProperties()[$name]['container'];
    }

    public function getProperties()
    {
        if($this->properties === null) {
            $this->addProperties([]);
        }

        $link = &$this->properties;
        return $link;
    }

    /**
     * @param $properties
     * @return $this
     * @throws \Exception
     */
    public function addProperties($properties)
    {
        if($this->properties === null) {
            $properties = array_merge(
                $this->getConfig()->get('properties'),
                $properties
            );
        }

        foreach($properties as $name => $options) {
            $this->addProperty($name, $options);
        }

        return $this;
    }

    /**
     * @param $name
     * @param array $options
     * @return $this|bool
     * @throws \Exception
     */
    public function addProperty($name, $options = [])
    {
        $default = [
            'name'      => '',
            //'type'      => self::PROPERTY_TYPE_TEXT,
            'default'   => '',
            'virtual'   => false, //true - если колонка не существует в базе данных
            'container' => null,
            'filters'   => [],
        ];

        if($name == 'time_create' && !$options['default']) {
            $default['default'] = date('Y-m-d h:i:s');
        }

        $options = array_merge($default, $options);

        if(array_key_exists($name, $this->properties[$name])) {
            return false;
        }

        switch ($options['type']) {
            case self::PROPERTY_TYPE_JSON:
                $containerObj = new Containers\Json();
                break;
            case self::PROPERTY_TYPE_NUM:
                $containerObj = new Containers\Num();
                break;
            case self::PROPERTY_TYPE_DATE:
                $containerObj = new Containers\Date();
                break;
            case self::PROPERTY_TYPE_TIME:
                $containerObj = new Containers\Time();
                break;
            default:
                $containerObj = new Containers\Text();
        }

        $options['container'] = $containerObj;

        if($name == '' || $name == $this->primary) {
            throw new \Exception('Invalid property "' . $options['name'] . '"');
        }

        $this->properties[$name] = $options;

        if($options['default']) {
            $containerObj->setSource($options['default']);
        }

        return $this;
    }

    public function d($die = true)
    {
        $select = clone $this->getLoadSelect(true);

        $dump = $this->getSql()->buildSqlString($select);

        if($die) die($dump);

        return $dump;
    }


    public function __toString()
    {
        return \Zend\Debug\Debug::dump($this->getProperties(), null ,false);
    }

    public function select()
    {
        $this->select = parent::select();

        $cols = array($this->primary);
        foreach($this->getProperties() as $field => $options) {
            if(!$options['virtual']) {
                $cols[] = $field;
            }
        }

        return $this->select;
    }

    public function rFill($data)
    {
        if(empty($data)) {
            return false;
        }

        $dataPlugins = [];
        $dataThis    = [];

        foreach($data as $name => $value) {
            $sepPos = strpos($name, '-');
            $pluginName = substr($name, 0, $sepPos);

            if($pluginName) {
                if(!isset($dataPlugins[$pluginName])) {
                    $dataPlugins[$pluginName] = [];
                }

                $key = substr($name, $sepPos + 1);
                $dataPlugins[$pluginName][$key] = $value;
            } else {
                $dataThis[$name] = $value;
            }
        }

        $this->fill($dataThis);

        foreach($dataPlugins as $pluginName => $pluginData) {
            $this->plugin($pluginName)->rFill($pluginData);
        }

        return $this;
    }

    /**
     * @param $data
     * @return $this
     * @throws \Exception
     */
    public function fill($data)
    {
        foreach($data as $name => $value) {
            if (!array_key_exists($name, $this->getProperties())) {
                if($name == $this->primary && $value) {
                    $this->id($value);
                    $this->loaded = true;
                }
                continue;
            }

            $this->getProperties()[$name]['container']->setSource($value)->isChanged(false);
        }

        return $this;
    }

    public function hasProperty($name)
    {
        return array_key_exists($name, $this->getProperties()) || $name == 'id';
    }

    /**
     * @param $name
     * @param $value
     * @param bool $filter
     * @return $this
     * @throws \Exception
     */
    public function set($name, $value, $filter = true)
    {
        if($name == 'id') {
            $this->id($value);
            return $this;
        }

        if (!$this->hasProperty($name) || $value === null) {
            return $this;
            throw new \Exception('Trying to set unknown property "' . $name . '"');
        }

        $property = &$this->getProperties()[$name];

        if($filter && $property['filters']['set']) {
            $value = call_user_func_array($property['filters']['set'], [$this, $value]);
        }

        $property['container']->set($value);

        return $this;
    }

    public function setVariables($variables, $filter = true)
    {
        foreach ($variables as $key => $val) {
            $this->set($key, $val, $filter);
        }

        return $this;
    }

    /**
     * @param $name
     * @param mixed $options
     * @return int|mixed|string|ArrayObject
     * @throws Exception
     */
    public function get($name, $options = null)
    {
        $this->load();

        if($name == 'id') {
            return $this->id();
        }

        if(!$this->hasProperty($name)) {
            return '';
        }

        $property = &$this->getProperties()[$name];

        $value = $property['container']->get($options);

        if($options['filter'] !== false && $property['filters']['get']) {
            $value = call_user_func_array($property['filters']['get'], [$this, $value]);
        }

        return $value;
    }

    public function getByTrace($trace)
    {
        $trace = ltrim($trace, '[');
        $trace = str_replace(']', '', $trace);

        $trace = explode('[', $trace);
        $traceCount = count($trace);

        $value = $this;
        for($i = 0; $i < $traceCount; $i++) {
            $tName = $trace[$i];

            if($value instanceof Entity) {
                if($value->hasProperty($tName)) {
                    $value = $value->$tName;
                } elseif($value->hasPlugin($tName)) {
                    $value = $value->$tName();
                } else {
                    $value = null;
                    break;
                }
            } elseif($value instanceof EntityCollection) {
                foreach ($value as $row) {
                    if($row->id() == $tName) {
                        $value = $row;
                        break;
                    }
                }
                if($value instanceof EntityCollection) {
                    $value = null;
                    break;
                }
            } elseif(is_array($value) || $value instanceof \ArrayAccess) {
                $value = $value[$tName];
            } elseif($value === null) {
                break;
            } else {
                $value = false;
                break;
            }
        }

        return $value;
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    /** @return $this */
    public function getClearCopy()
    {
        $class = get_called_class();
        return new $class();

        /*$copy = clone $this;
        $copy->clear();

        return $copy;*/
    }

    /*public function getCopy($type = 'full')
    {
        $class = get_class($this);
        $clone = new $class;

        if($type == 'clear') {
            return $clone;
        }

        foreach($clone->properties as $name => &$property) {
            $property['container'] = clone $property['container'];
            $property['container']->isChanged(true);
        }

        foreach($this->getConfig()->get('plugins') as $pluginName => $pluginOpts) {
            if(is_array($pluginOpts) && $pluginOpts['independent']) {
                continue;
            }

            if(!($plugin = $this->plugin($pluginName)->load())) {
                continue;
            }

            $this->plugins[$pluginName]['object'] = $plugin->getCopy()->setParent($this);
        }

        $this->id(0);
    }*/

    public function __clone()
    {
        $this->clearSelect();

        foreach($this->properties as $name => &$property) {
            $property['container'] = clone $property['container'];
            $property['container']->isChanged(true);
        }

        foreach($this->getConfig()->get('plugins') as $pluginName => $pluginOpts) {
            if(is_array($pluginOpts) && $pluginOpts['independent']) {
                continue;
            }

            if(!($plugin = $this->plugin($pluginName)->load())) {
                continue;
            }

            unset($this->plugins[$pluginName]);
            $this->plugins[$pluginName]['object'] = (clone $plugin)->setParent($this);
        }

        $this->id(0);
        $this->loaded = false;
        //$this->saved = false;

        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    /*protected function getCacheName($name = '') {
        return 'entity-' . str_replace('_', '-', $this->table()) . ($name ? '-' . $name : '');
    }*/

    /**
     * @return bool
     * @throws \Exception
     */
    /*protected function cacheLoad()
    {
        if(!$this->cacheEnabled || !$this->getCacheAdapter()) {
            return false;
        }

        $cacheName = $this->getCacheName(crc32($this->getSql()->buildSqlString($this->select())));

        if($data = $this->getCacheAdapter()->getItem($cacheName)) {
            $this->fill($data);
            return true;
        }

        return false;
    }*/

    /**
     * @return bool
     */
    /*protected function cacheSave()
    {
        if(!$this->cacheEnabled || !$this->getCacheAdapter()) {
            return false;
        }

        $cacheName = $this->getCacheName(crc32($this->getSql()->buildSqlString($this->select())));
        $this->getCacheAdapter()->setItem($cacheName, $this->serialize[]);
        $this->getCacheAdapter()->setTags($cacheName, [$this->table()]);

        return true;
    }

    protected function cacheClear()
    {
        if(!$this->getCacheAdapter()) {
            return false;
        }

        $this->getCacheAdapter()->clearByTags([$this->table()]);
        return true;
    }*/

    /**
     * @param $name
     * @return bool
     * @throws \Exception
     */
    public function hasPlugin($name)
    {
        if($this->plugins[$name]) {
            return true;
        }

        if($this->getConfig()->get('plugins')[$name]) {
            return true;
        }

        return false;
    }

    /**
     * @param $data
     * @param int $deep
     * @return $this
     * @throws \Exception
     */
    public function unserializeArray($data, $load = true)
    {
        //dd($data);
        $dataPlugins = [];

        if($data['id'] && $load) {
            $this->id($data['id'])->load();
        }

        foreach($data as $name => $value) {
            if($this->hasProperty($name)) {
                if(is_array($value)) {
                    if(!($this->getProperties()[$name]['container'] instanceof Containers\Text)) {
                        $this->set($name, $value);
                        continue;
                    }
                } else {
                    $this->set($name, $value);
                    continue;
                }
            }

            if($this->hasPlugin($name)) {
                if(!isset($dataPlugins[$name])) {
                    $dataPlugins[$name] = [];
                }

                $dataPlugins[$name] = $value;
            }
        }

        foreach($dataPlugins as $pluginName => $pluginData) {
            $this->plugin($pluginName)->unserializeArray($pluginData, false);
        }

        $this->loaded = true;

        return $this;
    }

    /**
     * @param array $result
     * @param int $deep
     * @return array
     * @throws Exception
     */
    public function serializeArray($deep = 0)
    {
        $this->load();

        /*$elName = function($name) use ($prefix){
            return $prefix ? $prefix . '[' . $name . ']' : $name;
        };*/

        $result['id'] = $this->id();
        foreach($this->getProperties() as $key => $val) {
            $result[$key] = $this->getProperties()[$key]['container']->serializeArray();
        }

        if(!$deep) return $result;

        foreach(array_keys($this->getConfig()->get('plugins')) as $pluginName) {
            $plugin = $this->plugin($pluginName);

            if($plugin->load()) {
                $result[$pluginName] = $plugin->serializeArray($deep - 1);
            }
        }

        return $result;
    }

    /* ArrayAccess */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

    /* Iterator */
    public function rewind()
    {
        $this->load();

        return reset($this->getProperties());
    }

    public function current()
    {
        $prop = current($this->getProperties());

        return $prop['container']->get();
    }

    public function key()
    {
        return key($this->getProperties());
    }

    public function next()
    {
        return next($this->getProperties());
    }

    public function valid()
    {
        $key = key($this->getProperties());
        return ($key !== null && $key !== false);
    }

    /**
     * @var Entity
     */
    protected $parent = null;

    public function setParent(Entity $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return EntityCollection
     */
    static public function getEntityCollection($options = [])
    {
        return EntityCollection::factory(get_called_class(), $options);
    }

    public function serialize($options = []) {
        $options = $options + [
                'fullSerialize' => true
            ];

        return Json::encode($this->serializeArray([], '', $options['fullSerialize']));
    }

    public function unserialize($data) {
        $this->__construct();
        $this->rFill(Json::decode($data));
    }

    public function __sleep() {
        parent::__sleep();
    }

    public function getEventManager() {
        return $this->getConfig()->getEventManager();
    }
}