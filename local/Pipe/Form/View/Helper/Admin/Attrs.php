<?php
namespace Pipe\Form\View\Helper\Admin;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;

class Attrs extends AbstractHelper
{
    public function render(ElementInterface $element)
    {
        $model = $element->getOption('model');

        $html =
            '<div class="attrs-list" data-name="' . $element->getName() . '">'
                .'<div class="notice">'
                    .'Удаление свойтсв также влечет удаление значений, привязанных к этому свойству.'
                    .'<div class="tooltip"><div class="tooltip-icon">?</div><div class="tooltip-desc">Пример свойства:<br>Цвет: Синий или Размер: XXL</i></div></div>'
                .'</div>'
                .'<div class="list">';

        foreach($model as $key => $value) {
            $html .=
                '<div class="row">'
                    .'<input type="text" name="' . $element->getName() . '[key][]" value="' . $key . '" disabled placeholder="Свойство"> '
                    .'<input type="text" name="' . $element->getName() . '[val][]" value="' . $value . '" disabled placeholder="Значение">'
                    .' <div class="btn btn-blue remove" data-id="' . $key . '"></div>'
                    .' <div class="btn btn-blue edit"></div>'
                .'</div>';
        }

        $html .=
                '</div>'
                .'<div class="row"><div class="btn btn-blue add">Добавить</div></div>'
            .'</div>';

        return $html;
    }

    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }
}