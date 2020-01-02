<?php
namespace Pipe\Db\Entity;

use Pipe\EventManager\EventManager;

class Config
{
    protected $config = [
        'table'         => null,
        'plugins'       => [],
        'properties'    => [],
        'events'        => [],
    ];

    public function setConfig(&$config)
    {
        if($config['parent'] && is_string($config['parent'])) {
            $parent = ConfigCollector::getInstance()->getConfig($config['parent']);
            unset($config['parent']);

            if(!$config = array_merge($parent->get(), $config)) {
                throw new \Exception('Can`t find parent config: ' . $parent);
            }
        }

        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * @param string $type
     * @return array|string
     * @throws \Exception
     */
    public function get($type = null)
    {
        if(!$type) {
            return $this->config;
        }

        $config = $this->config[$type];

        if(is_string($config)) {
            return $config;
        }

        if($config['parent'] && is_string($config['parent'])) {
            $collector = ConfigCollector::getInstance();
            $config = array_merge($collector->getConfig($config['parent'])->getConfig($type), $config);
            unset($config['parent']);
        }

        return $config;
    }

    /** @var EventManager */
    protected $eventManager;

    public function getEventManager()
    {
        if ($this->eventManager) {
            return $this->eventManager;
        }

        $this->eventManager = new EventManager();

        foreach ($this->get('events') as $event) {
            $this->eventManager->attach($event['events'], $event['function'], $event['priority'] ?? 1);
        }

        return $this->eventManager;
    }
}