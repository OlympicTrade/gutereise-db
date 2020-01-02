<?php

namespace Pipe\Cache;

use Zend\Cache\Storage\Adapter\AbstractAdapter as Adapter;

interface CacheAwareInterface
{
    public function setCacheAdapter(Adapter $cache);
}