<?php
namespace Pipe\Cache;

use Zend\Serializer\Adapter\PhpSerialize;

class Cache
{
    /** @var \Cache\Adapter\Common\AbstractCachePool */
    protected $pool;

    /** @var \Cache\Adapter\Common\CacheItem */
    protected $item;

    /** @var \Zend\Serializer\Adapter\PhpSerialize */
    static protected $serializer;

    public function __construct($pool, $key)
    {
        self::$serializer = new PhpSerialize();
        $this->pool = $pool;
        $this->item = $this->pool->getItem($key);
    }

    public function getItem()
    {
        return $this->item;
    }

    public function has()
    {
        return $this->item->isHit();
    }

    public function set($value)
    {
        $this->item->set(self::$serializer->serialize($value));

        return $this;
    }

    public function get()
    {
        return self::$serializer->unserialize($this->item->get());
    }

    public function setTags($tags)
    {
        $this->item->setTags((array) $tags);

        return $this;
    }

    public function save()
    {
        $this->pool->save($this->item);

        return $this;
    }
}