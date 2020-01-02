<?php
namespace Pipe\View\Helper;

use Zend\I18n\View\Helper\AbstractTranslatorHelper;

class Breadcrumbs extends AbstractTranslatorHelper
{
    public function __invoke($crumbs, $delimiter = '/')
    {
        $translator = $this->getTranslator();

        $html =
            '<div class="breadcrumbs">';

        $view = $this->getView();

        for($i = 0; $i < count($crumbs) - 1; $i++) {
            $html .=
                '<a href="' . $crumbs[$i]['url'] . '">' . $translator->translate($crumbs[$i]['name']) . '</a>' .  ' ' . $delimiter . ' ';
        }

        $html .=
                '<span>' .  $crumbs[$i]['name'] . '</span>';

        $html .=
            '</div>';

        return $html;
    }
}