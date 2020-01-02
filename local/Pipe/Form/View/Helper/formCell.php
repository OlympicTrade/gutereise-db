<?php
namespace Pipe\Form\View\Helper;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;
use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use Zend\Form\ElementInterface;

class formCell extends AbstractTranslatorHelper
{
    /**
     * @param ZElement $element
     * @param array $options
     * @return $this|string
     */
    public function __invoke($element, $options = [])
    {
        if (!$element) {
            return $this;
        }

        $html = '';

        $label = $element->getLabel();

        $elType = $element->getAttribute('type');

        if(in_array(get_class($element), [PElement\ECollection::class, PElement\EArray::class])) {
            return $this->getView()->formElement($element);
        }

        if (!$element->getAttribute('class')) {
            switch ($elType) {
                case 'text':
                    $element->setAttribute('class', 'std-input');
                    break;
                case 'number':
                    $element->setAttribute('class', 'std-input');
                    break;
                case 'tel':
                    $element->setAttribute('class', 'std-input');
                    break;
                case 'textarea':
                    $element->setAttribute('class', 'std-textarea');
                    break;
                case 'select':
                    $element->setAttribute('class', 'std-select');
                    break;
                case 'file':
                    $element->setAttribute('class', 'std-file');
                    break;
                default:
            }
        }

        if ($elType == 'file') {
            $html .= $this->renderFile($label, $element);
        } elseif($elType == 'checkbox') {
            $html .= $this->renderCheckbox($label, $element, $options);
        } elseif(strpos('editor', $element->getAttribute('class')) === false) {
            $html .= $this->renderFull($label, $element, $options);
        } else {
            $html .= $this->renderSmall($label, $element, $options);
        }

        return $html;
    }

    protected function renderFile($label, $element)
    {
        $html =
            '<div class="element std-file">';

        if($file = $element->getOption('file')) {
            $html .=
                '<a class="old" target="_blank" href="' . $file . '">Текущий файл</a>';
        }

        $html .=
                '<label class="new">'.
                    '<div class="text">' . $label . '</div>'.
                    $this->getView()->formElement($element).
                '</label>'.
            '</div>';

        return $html;
    }

    protected function renderCheckbox($label, $element, $options)
    {
        $html =
            '<label class="std-checkbox">'.
                $this->getView()->formElement($element).
                '<span>' . $label . '</span>'.
            '</label>';

        return $html;
    }

    protected function renderFull($label, $element, $options)
    {
        $html = $options['html'] ?? '';

        $html =
            '<div class="element">'
                .'<div class="label">' . $label . '</div>'
                . $this->getView()->formElement($element) . $this->getView()->formElementErrors($element)
                . $html
            .'</div>';

        return $html;
    }

    protected function renderSmall($label, $element, $options)
    {
        $element->setAttribute('placeholder', $label);

        return $this->getView()->formElement($element) . $this->getView()->formElementErrors($element);
    }
}