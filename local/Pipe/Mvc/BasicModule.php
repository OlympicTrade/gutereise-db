<?php

namespace Pipe\Mvc;

trait BasicModule
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}