<?php
namespace Pipe\View\Helper;

use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use Zend\Json\Json;

class Link extends AbstractTranslatorHelper
{
    public function __invoke($link, $urlOnly = false)
    {
        if(strpos($link, '@')) {
            //Email
            $url = 'mailto:' . $link;
        } else {
            //Phone
            $url = 'tel:' . str_replace(array('(', ')', ' ', '-'), array(''), $link);
        }

        if(!$urlOnly) {
            return '<a href="' . $url . '">' . $link . '</a>';
        }

        return $url;
    }
}