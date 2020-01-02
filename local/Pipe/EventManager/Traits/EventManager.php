<?php
namespace Pipe\EventManager\Traits;

use Pipe\EventManager\EventManager as PEventManager;

trait EventManager {
    /** @var PEventManager */
    protected $eventManager;

    /** @return PEventManager */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->eventManager = new PEventManager();
        }

        return $this->eventManager;
    }
}