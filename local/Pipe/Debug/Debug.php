<?php
namespace Pipe\Debug;

class Debug
{
    public static function dump($obj)
    {
        echo '<pre>';
        var_dump($obj);
        echo '</pre>';
    }
}
