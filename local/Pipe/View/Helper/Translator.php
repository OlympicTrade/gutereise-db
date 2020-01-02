<?php
namespace Pipe\View\Helper;

//use Translator\Model\Translator as Tr;
use Zend\View\Helper\AbstractHelper;

class Translator extends AbstractHelper
{
    public function __invoke($str)
    {
        return $str;
        //return Tr::getInstance()->translate($str);
    }
}