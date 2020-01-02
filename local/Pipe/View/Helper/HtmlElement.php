<?php
namespace Pipe\View\Helper;

use Zend\View\Helper\AbstractHelper;

class HtmlElement extends AbstractHelper
{
    public function __invoke($element, $attrs = [], $text = '')
    {
        $attrsHtml = '';
        foreach ($attrs as $key => $val) {
            if($val == null) {
                $attrsHtml .= ' ' . $key;
            } else {
                $attrsHtml .= ' ' . $key . '="' . $val . '"';
            }
        }

        $html =
            '<' . $element . $attrsHtml . '>'.
                $text.
            '</' . $element . '>';

        return $html;
    }


}