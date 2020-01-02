<?php
namespace Application\Admin\View\Helper;


use Zend\View\Helper\AbstractHelper;

class SmartList extends AbstractHelper
{
    public function __invoke($options)
    {
        $options = $options + [
            'class' => '',
            'header' => true,
        ];

        $html =
            '<div class="smart-list ' . $options['class'] . '">';

        if ($options['header']) {
            $html .=
                '<div class="header cols">';

            foreach ($options['fields'] as $field) {
                $el = $field['el'];
                $html .=
                    '<div class="col-' . $field['width'] . '">' . $el->getLabel() . '</div>';
            }

            $html .=
                '</div>';
        }

        $html .=
                '<div class="list">'.
                    '<div class="row cols first"><div class="delimiter"></div></div>'.
                '</div>'.
                '<div class="row cols pattern">';

        foreach ($options['fields'] as $field) {
            $el = $field['el'];
            $el->setAttributes(['data-name' => $options['name'] . $el->getName()]);
            $el->setName('_');

            if (!$el->getAttribute('class')) {
                switch ($el->getAttribute('type')) {
                    case 'text':
                        $el->setAttribute('class', 'std-input');
                        break;
                    case 'number':
                        $el->setAttribute('class', 'std-input');
                        break;
                    case 'tel':
                        $el->setAttribute('class', 'std-input');
                        break;
                    case 'textarea':
                        $el->setAttribute('class', 'std-textarea');
                        break;
                    case 'select':
                        $el->setAttribute('class', 'std-select');
                        break;
                    default:
                }
            }

            $html .=
                '<div class="col col-' . $field['width'] . '">'.
                    $this->getView()->formElement($el).
                '</div>';
        }

        $html .=
                '<input type="hidden" class="sort" data-name="' . $options['name'] . '[sort]">'.
                '<div class="btns">'.
                    ' <span class="btn sort"></span>'.
                    ' <span class="btn del"></span>'.
                '</div>'.
                '<div class="delimiter"></div>'.
            '</div>'.
        '</div>';

        return $html;
    }
}