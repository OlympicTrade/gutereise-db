<?php
namespace Pipe\Form\View\Helper;

use Zend\Form\Element;
use Zend\Form\View\Helper\AbstractHelper;

/*
Example of usage
$formFactory = new FormFactory($view, $form);
$formFactory->structure([
    ['id'],
    [
        ['width' => 50, 'element' => 'test1'],
        ['width' => 50, 'element' => 'test2'],
    ],
    [
        'type'   => 'panel',
        'name'   => 'Заголовок',
        'children' => [
            ['width' => 50, 'element' => 'test1'],
            ['width' => 50, 'element' => 'test2'],
        ],
    ],
]);
*/

class FormFactory extends AbstractHelper
{
    use Traits\Form;

    protected $prefix;
    public function setPrefix($prefix) {
        $this->prefix = $prefix;
        return $this;
    }

    public function __construct($view = null, $form = null) {
        if($view) $this->setView($view);
        if($form) $this->setForm($form);
    }

    public function open()
    {
        $view = $this->getView();

        $module  = $view->module;
        $section = $view->section;

        $class = 'table-edit-' . ($module == $section ? $module : $module . '-' . $section);

        return
            '<form action="" method="post" class="table-edit std-form ' . $class . '" data-module="' . $module . '" data-section="' . $section . '">';
    }

    public function close()
    {
        return
            '</form>';
    }

    public function structure($structure) {
        $structure = (array) $structure;
        $html = '';

        foreach($structure as $data) {
            switch ($data['type']) {
                case 'tabs':
                    $html .= $this->tabs($data);
                    break;
                case 'tag':
                    $html .= $this->tag($data);
                    break;
                case 'panel':
                    $html .= $this->panel($data);
                    break;
                case 'preset':
                    $html .= $this->preset($data);
                    break;
                case 'html':
                    $html .= $this->html($data);
                    break;
                default:
                    $html .= $this->formRow($data);
            }
        }

        return $html;
    }

    public function html($data) {
        return is_string($data) ? $data : $data['html'];
    }

    public function tag($data) {
        $data = $data + [
            'tag'   => 'h2',
            'attrs' => [],
            'text'  => '',
        ];

        return $this->getView()->htmlElement($data['tag'], [], $data['name'] ?? $data['text']);
    }

    public function tabs($data) {
        $html =
            '<div class="tabs std-tabs">'.
                '<div class="tabs-header">';

        $i = 0;
        foreach ($data['tabs'] as $tab) {
            !$tabId = $tab['id'] ?: $tabId = ++$i;

            $html .=
                '<div class="tab" data-tab="' . $tabId . '">' . $tab['header'] . '</div>';
        }

        $html .=
                '</div>'.
                '<div class="tabs-body">';

        $i = 0;
        foreach ($data['tabs'] as $tab) {
            !$tabId = $tab['id'] ?: $tabId = ++$i;

            $html .=
                '<div class="tab" data-tab="' . $tabId . '">' . $this->structure($tab['body']) . '</div>';
        }

        $html .=
                '</div>'.
            '</div>';

        return $html;
    }

    public function panel($data) {

        return $this->getView()->htmlElement('fieldset', $data['attrs'],
            ($data['name'] ? '<legend>' . $data['name'] . '</legend>' : '').
            (!$data['children'] ?: $this->structure($data['children']))
        );
    }

    public function formRow($data)
    {
        $data = (array) $data;
        $view = $this->getView();

        $options = [
            'row' => true,
        ];
        if($data['options']) {
            $options = $data['options'] + $options;
            unset($data['options']);
        }

        $html = '';
        $rowHtml = '';
        foreach ($data as $cell) {
            if(is_array($cell) && $cell['type']) {
                $rowHtml .=
                    '<div class="col-' . $cell['width'] . '">'.
                        $this->structure([$cell]).
                    '</div>';
                continue;
            }

            if(is_array($cell) && $cell['children']) {
                $rowHtml .=
                    '<div class="col-' . $cell['width'] . '">'.
                        $this->structure([$cell['children']]).
                    '</div>';
                continue;
            }

            if(is_string($cell)) {
                $cell = ['element' => $cell];
            }

            $element = is_string($cell['element']) ? $this->form->get($this->prefix . $cell['element']) : $cell['element'];

            if($element instanceof Element\Hidden) {
                $html .= $view->formElement($element);
                continue;
            }

            $cell = $cell + ['width' => '100'];
            if(!empty($cell['label'])) {
                $element->setLabel($cell['label']);
            }

            $rowHtml .=
                '<div class="col-' . $cell['width'] . '">'.
                    $view->formCell($element, $cell).
                '</div>';
        }

        if($rowHtml) {
            $html .= '<div class="' . ($options['row'] ? 'row ' : '') . 'cols">' . $rowHtml . '</div>';
        }

        return $html;
    }

    public function preset($data) {
        switch ($data['name']) {
            case 'basic':
                $elements = [];

                if($this->form->has('name')) {
                    $elements[] = 'name';
                }

                if($this->form->has('contacts')) {
                    $elements[] = 'contacts';
                }

                if($this->form->has('comment')) {
                    $elements[] = 'comment';
                }

                $html = $this->structure($elements);
                break;
            default:
                throw new \Exception('Unknown preset');
        }

        return $html;
    }
}