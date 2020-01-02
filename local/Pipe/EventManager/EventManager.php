<?php
namespace Pipe\EventManager;

class EventManager extends \Zend\EventManager\EventManager
{
    public function attach($eventName, callable $listener, $priority = 1)
    {
        $eventName = (array) $eventName;
            array_walk($eventName, function ($id) use ($listener, $priority) {
                parent::attach($id, $listener, $priority);
            });

        return $this;
    }
}