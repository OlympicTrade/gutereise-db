<?php
namespace Pipe\Db\Entity;

use Pipe\StdLib\Singleton;

class ConfigCollector
{
    use Singleton;

    protected $config = [];

    public function getProperties($entity)
    {
        $props = &$this->getConfig($entity);

        if($props['parent']) {
            $config = array_merge($props, $this->getProperties($props['parent']));
            unset($config['parent']);
        }

        return $props;
    }

    public function getConfig($entity = null)
    {
        if(is_string($entity)) {
            $entityClass = $entity;
        } elseif(is_object($entity)) {
            $entityClass = get_class($entity);
        } else {
            throw new \Exception('Unknown type of config key');
        }

        if(!isset($this->config[$entityClass])) {
            if(!class_exists($entityClass)) {
                throw new \Exception('Can`t find class: "' . $entityClass . '"');
            }

            /** @var Entity $entityClass */
            if(!($configArr = $entityClass::getFactoryConfig())) {
                throw new \Exception('Can`t find config in class: "' . $entityClass . '"');
            }

            $this->config[$entityClass] = (new Config())->setConfig($configArr);
        }

        return $this->config[$entityClass];
    }
}