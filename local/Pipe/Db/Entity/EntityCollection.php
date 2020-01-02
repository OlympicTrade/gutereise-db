<?php
/**
 * Example
 * //Create entity collection ans set prototype
 * $collection = new EntityCollection();
 * $collection->getPrototype(new Entity());
 *
 * $collection->setSelect(select object);
 *
 * foreach($collection as $entity) {
 *    echo $entity['name'] . '<br>';
 * }
 */

namespace Pipe\Db\Entity;

use Pipe\Db\AbstractDb;

use Pipe\Db\Plugin\PluginInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Pipe\Db\ResultSet\ResultSet;

use Iterator;

class EntityCollection extends AbstractDb implements Iterator, AdapterInterface, PluginInterface
{
    /**
     * @var Entity
     */
    protected $prototype = null;

    /**
     * @var Select
     */
    protected $select = null;

    /**
     * @var array
     */
    protected $data = null;

    protected $listToRemove = [];

    /**
     * @var int
     */
    protected $rowCount = null;

    /**
     * @return $this|bool
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function load()
    {
        if($this->loaded) {
            return $this;
        }

        if($this->cacheLoad()) {
            $this->loaded = true;
            return $this;
        }

        $result = $this->execute($this->getLoadSelect());

        if(!$result || !$result->count()) {
            return false;
        }

        foreach ($result as $row) {
            $this->data[$row['id']] = (clone $this->getPrototype())->rFill($row);
        }

        $this->loaded = true;

        $this->cacheSave();

        return $this;
    }

    public function clear()
    {
        $this->loaded = false;
        $this->data = null;
    }

    /**
     * @return Entity
     */
    public function getPrototype()
    {
        return $this->prototype;
    }

    /**
     * @param Entity $prototype
     * @return $this
     */
    public function setPrototype($prototype)
    {
        $this->setTable($prototype->table());
        $this->prototype = $prototype;
        $this->loaded = false;

        return $this;
    }

    public function getLoadSelect()
    {
        $select = clone $this->select();

        if($this->getParent() && $this->getPrototype()->hasProperty('depend')) {
            $select->where(['depend' => $this->getParent()->id()]);
        }

        return $select;
    }

    /**
     * @param $name
     * @param $value
     * @param bool $clear
     * @return $this
     */
    public function set($name, $value, $clear = false)
    {
        foreach($this as $entity) {
            $entity->set($name, $value, $clear);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function save()
    {
        foreach($this->listToRemove as $key => $entity) {
            $entity->remove();
            unset($this->listToRemove[$key]);
        }

        foreach($this as $entity) {
            $entity->save();
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function remove()
    {
        $this->load();

        foreach($this->listToRemove as $key => $entity) {
            $entity->remove();
            unset($this->listToRemove[$key]);
        }

        foreach($this->data as $key => $entity) {
            $entity->remove();
            unset($this->data[$key]);
        }

        $this->loaded = false;

        return $this;
    }

    /**
     * @param int $offset
     * @param int $itemCountPerPage
     * @return $this|array
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->select()->limit($offset);
        $this->select()->offset($itemCountPerPage);

        $this->load();

        return $this;
    }

    /**
     * @param $entity
     * @return string
     * @throws \Exception
     */
    public function addEntity($entity)
    {
        if(is_array($entity)) {
            $newEntity = $this->getPrototype()->getClearCopy();

            if($newEntity->hasProperty('depend') && $this->getParent()) {
                $newEntity->setParent($this->getParent());
            }

            $newEntity->unserializeArray($entity);
        } else {
            $newEntity = $entity;
        }

        $id = 'new-' . count($this->data);
        $this->data[$id] = $newEntity;

        return $id;
    }

    public function delEntity($id)
    {
        $this->load();

        if(isset($this->data[$id])) {
            $this->listToRemove[] = $this->data[$id];
            unset($this->data[$id]);
            return $this;
        }

        $entity = $this->getPrototype()->getClearCopy();
        $entity->id($id);

        if($entity->load()) {
            $this->listToRemove[] = $entity;
        }

        if(isset($this->data[$id])) {
            unset($this->data[$id]);
        }

        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function rFill($data)
    {
        foreach($data as $row) {
            $this->data[] = $row;
        }

        return $this;
    }

    /**
     * @return bool|Entity
     */
    public function getFirst()
    {
        $arr = $this->rewind();
        return is_array($arr) ? $arr->current() : false;
    }

    /**
     * @return int
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function count()
    {
        if($this->rowCount !== null) {
            return $this->rowCount;
        }

        if($this->cacheEnabled && $this->getCacheAdapter()) {
            $cacheName = @$this->getCacheName('count-' . md5($this->select()->getSqlString()));

            if(($this->rowCount = $this->getCacheAdapter()->getItem($cacheName)) !== null) {
                return $this->rowCount;
            }
        }

        $select = $this->getLoadSelect();

        $select->reset(Select::LIMIT);
        $select->reset(Select::OFFSET);
        $select->reset(Select::ORDER);
        $select->reset(Select::HAVING);
        $select->reset(Select::COLUMNS);

        $select->columns(array('id'));

        $countSelect = new Select();
        $countSelect->columns(array('c' => new Expression('COUNT(*)')));
        $countSelect->from(array('entity_select' => $select));

        $statement = $this->getSql()->prepareStatementForSqlObject($countSelect);
        $result    = $statement->execute();
        $row       = $result->current();

        $this->rowCount = $row['c'];

        if($this->cacheEnabled && $this->getCacheAdapter()) {
            $cacheName = @$this->getCacheName('count-' . md5($this->select()->getSqlString()));
            $this->getCacheAdapter()->setItem($cacheName, $this->rowCount);
            $this->getCacheAdapter()->setTags($cacheName, $this->getCacheTags());
        }

        return $this->rowCount;
    }

    /**
     * @param int $page
     * @param int $rows
     * @param array $options
     * @return Paginator
     */
    public function getPaginator($page = 1, $rows = 10, $options = array())
    {
        $resultSet = new ResultSet();
        $resultSet->setPrototype($this->getPrototype());
        $paginatorAdapter = new DbSelect(
            $this->getLoadSelect(),
            $this->getDbAdapter(),
            $resultSet
        );

        $paginator = new Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($rows);

        return $paginator;
    }

    protected function getCacheName($name = '') {
        return 'db-entity-collection-' . str_replace('_', '-', $this->table()) . ($name ? '-' . $name : '');
    }

    /**
     * @return array
     */
    protected function getCacheTags()
    {
        $tags = array($this->table());
        foreach($this->joins as $join) {
            $tags = array_merge($tags, $join['name']);
        }
        return $tags;
    }

    /**
     * @return bool
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    protected function cacheLoad()
    {
        if(!$this->cacheEnabled || !$this->getCacheAdapter()) {
            return false;
        }

        $cacheName = @$this->getCacheName(md5($this->select()->getSqlString()));


        if($data = $this->getCacheAdapter()->getItem($cacheName)) {
            foreach($data as $row) {
                $this->addEntity($row);
            }
            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    protected function cacheSave()
    {
        if(!$this->cacheEnabled || !$this->getCacheAdapter()) {
            return false;
        }

        $data = array();

        $cacheName = @$this->getCacheName(md5($this->select()->getSqlString()));

        $this->getCacheAdapter()->setItem($cacheName, $data);
        $this->getCacheAdapter()->setTags($cacheName, $this->getCacheTags());

        return true;
    }

    public function serializeArray($deep = 3) {
        $deep--;
        $result = [];

        foreach ($this as $item) {
            $result[] = $item->serializeArray($deep);
        }

        return $result;
    }

    /**
     * @param $data
     * @return $this
     * @throws \Exception
     */
    public function unserializeArray($data)
    {
        if(empty($data)) return $this;

        $this->load();

        switch (key($data)) {
            /*case 'ecollection':
                $this->unserializeECollection(array_shift($data));
                break;*/
            case 'echeckbox':
                $this->unserializeECheckbox(array_shift($data));
                break;
            default:
                $this->unserializeECollection($data);
                //throw new \Exception('Unknown form data format: ' . key($data));
        }

        return $this;
    }

    protected function unserializeECheckbox($data)
    {
        if(!$data['ids']) {
            $this->remove();
            return $this;
        }

        $field = $data['field'];

        $ids = $data['ids'];
        foreach ($this->data as $key => $val) {
            if(($idKey = array_search($val->get($field), $ids)) !== false) {
                unset($ids[$idKey]);
            } else {
                $this->delEntity($key);
            }
        }

        foreach ($ids as $id) {
            $this->addEntity([
                $field => $id
            ]);
        }

        return $this;
    }

    protected function unserializeECollection($data)
    {
        $ids = [];

        foreach ($data as $key => $val) {
            if($key == 'tmp') continue;

            if(strpos($key, 'new-') === 0) {
                $ids[] = $this->addEntity($val);
                continue;
            }

            if(array_key_exists($key, $this->data)) {
                $ids[] = $key;
                $this->data[$key]->unserializeArray($val);
            }
        }

        if($this->data) {
            foreach ($this->data as $key => $val) {
                if (!in_array($key, $ids) && !strpos($key, 'new-')) {
                    $this->delEntity($key);
                }
            }
        }

        return $this;
    }

    /*public function plugin()
    {
        $plugin = new Collection();
        $plugin->setPrototype($this->getPrototype());
        return $plugin;
    }*/

    public function setSelect(Select $select)
    {
        $this->rowCount = null;
        $this->data = null;

        return parent::setSelect($select);
    }

    public function rewind()
    {
        $this->load();

        if($this->data) {
            reset($this->data);
        }

        return $this;
    }

    public function current()
    {
        return current($this->data);
    }

    public function key()
    {
        return key($this->data);
    }

    public function next()
    {
        next($this->data);
    }

    public function valid()
    {
        if($this->data == null) {
            return false;
        }
        $key = key($this->data);
        return $key !== null && $key !== false;
    }

    public function d($die = true)
    {
        $select = clone $this->getLoadSelect();

        $dump = $this->getSql()->buildSqlString($select);

        if($die) die($dump);

        return $dump;
    }

    static public function factory($prototype, $options = []) {
        $class = get_called_class();
        $collection = (new $class)->setPrototype(new $prototype());

        if($options['depend']) {
            $collection->select()->where(['depend' => $options['depend']]);
        }

        if($options['sort']) {
            $collection->select()->order($options['sort']);
        }

        return $collection;
    }

    /* PluginInterface */
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

    //clone
    public function __clone()
    {
        $this->load();

        foreach ($this->data as $key => $entity) {
            $this->data[$key] = clone $entity;
            $this->data[$key]->setParent($this->getParent());
        }
    }
}