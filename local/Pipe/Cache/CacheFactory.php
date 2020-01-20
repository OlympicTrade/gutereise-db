<?php
namespace Pipe\Cache;

use Cache\Adapter\Common\AbstractCachePool as Adapter;
use Pipe\String\Translit;

class CacheFactory
{
    /**
     * @var Adapter[]
     */
    protected static $staticAdapters = array();

    /**
     * @param Adapter $adapter
     * @param string $cache
     */
    public static function setAdapter(Adapter $adapter, $cache = 'data')
    {
        static::$staticAdapters[$cache] = $adapter;
    }

    /**
     * @param string $key
     * @return Cache
     */
    public static function getCache($key, $cache = 'data')
    {
        return new Cache(
            static::$staticAdapters[$cache],
            strtolower(Translit::ruToEn($key))
        );
    }
}
