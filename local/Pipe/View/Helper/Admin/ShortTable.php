<?php
namespace Pipe\View\Helper\Admin;

use Pipe\Form\Element\Time;
use Pipe\Form\Element\ETreeSelect;
use Zend\Form\Element\Select;
use Zend\View\Helper\AbstractHelper;

class ShortTable extends AbstractHelper
{
    public function __invoke($options)
    {
        $options = $options + [
            'sort' => true,
        ];

        $items = $options['items'];
        $prefix = $options['prefix'];
        $formName = $options['name'];
        $formClass = $formName . '-short-form';
        $fields = $options['fields'];

        $html =
        '<div class="short-form ' . $formClass . '" data-prefix="' . $prefix . '" data-name="' . $formName . '">'
        .'<div class="form">'
            .'<input type="hidden" name="' . $prefix . '[tmp]">';

        if($options['html_before']) {
            $html .= $options['html_before'];
        }

        $html .=
        '</div>'
        .'<div class="table">'
            .'<div class="header">';

        foreach ($fields as $field => $opts) {
            $html .=
                '<div class="cell" style="width: ' . $opts['width'] . 'px">' .  $opts['name'] . '</div>';
        }

        $html .=
                '<div class="cell row-acts">';

        if($options['btns']) {
            foreach ($options['btns'] as $button) {
                $html .= ' <span class="btn ' . $button['class'] . '"><i class="' . $button['icon'] . '"></i></span>';
            }
        }

        $html .=
                    '<span class="btn green item-add" data-form="#' . $prefix . $formName . '-form"><i class="fa fa-plus"></i></span>'
                .'</div>'
            .'</div>'
            .'<div class="list">';

        $i = 0;
        foreach($items as $item) {
            $i++;

            if($item->id() > 0) {
                $idStr = $item->id();
            } else {
                $idStr = 'new-' . $i;
            }

            $elName = $prefix . '[' . $idStr . ']';

            $html .=
                '<div class="item" data-id="' . $idStr . '">';

            foreach ($fields as $field => $opts) {
                $moduleName = !empty($opts['module']) ? $opts['module'] : null;
                $sectionName = !empty($opts['section']) ? $opts['section'] : $moduleName;

                $html .=
                    '<div class="cell" style="width: ' . $opts['width'] . 'px"' .
                        ($moduleName ? ' data-module="' . $moduleName . '"' : '') .
                        ($sectionName ? ' data-section="' . $sectionName . '"' : '') .
                    '>'
                        .$this->renderElement([
                            'formClass' => $formClass,
                            'formName'  => $formName,
                            'name'  => $elName,
                            'field' => $field,
                            'opts'  => $opts,
                            'val'   => $item->get($field),
                        ]);

                if($moduleName) {
                    $html .=
                        '<div class="cell-acts">'
                            .'<span data-action="edit"><i class="fas fa-plus"></i></span>'
                            .'<span data-action="view"><i class="fas fa-search"></i></span>'
                        .'</div>';
                }

                $html .=
                    '</div>';
            }

            $html .=
                    '<div class="cell row-acts">'
                        .'<input type="hidden" value="' . $idStr . '" name="' . $elName . '[id]">';

            if($options['sort']) {
                $html .=
                        '<input type="hidden" class="sort" value="" name="' . $elName . '[sort]">'.
                        '<span class="btn purple item-sort sm"><i class="fas fa-sort"></i></span>';
            }

            $html .=
                        '<span class="btn red item-del sm"><i class="fa fa-times"></i></span>'
                        .'<span class="btn blue item-copy sm"><i class="far fa-copy"></i></span>'
                    .'</div>'
                .'</div>';
        }

        $html .=
            '</div>'
        .'</div>';

        $html .=
            '<div class="item pattern">'; //style="display: none;" id="' . $prefix . '-' . $formName . '-form"

        foreach ($fields as $field => $opts) {
            $elName = $prefix . '[_ID_]';
            $moduleName  = !empty($opts['module']) ? $opts['module'] : null;
            $sectionName = !empty($opts['section']) ? $opts['section'] : $moduleName;

            $html .=
                '<div class="cell" style="width: ' . $opts['width'] . 'px"' .
                    ($moduleName ? ' data-module="' . $moduleName . '"' : '') .
                    ($sectionName ? ' data-section="' . $sectionName . '"' : '') .
                '>'
                    .$this->renderElement([
                        'formClass' => $formClass,
                        'formName'  => $formName,
                        'name'      => $elName,
                        'data'      => true,
                        'field'     => $field,
                        'opts'      => $opts,
                        'default'   => $opts['default'],
                    ]);

            if($moduleName) {
                $html .=
                    '<div class="cell-acts">'
                        .'<span data-action="edit"><i class="fas fa-plus"></i></span>'
                        .'<span data-action="view"><i class="fas fa-search"></i></span>'
                    .'</div>';
            }

            $html .=
                '</div>';
        }

        $html .=
                '<div class="cell row-acts">'
                    .'<input type="hidden" value="" name="' . $elName . '[id]">'
                    .'<input type="hidden" class="sort" value="" name="' . $elName . '[sort]">'
                    .'<span class="btn purple item-sort sm"><i class="fas fa-sort"></i></span>'
                    .'<span class="btn red item-del sm"><i class="fa fa-times"></i></span>'
                    .'<span class="btn blue item-copy sm"><i class="far fa-copy"></i></span>'
                .'</div>'
            .'</div>';

        $html .=
        '</div>';

        return $html;
    }

    protected function renderElement($options)
    {
        $options = $options + [
            'name'       => '',
            'data'       => false,
            'val'        => '',
        ];

        $opts = $options['opts'];
        $html = '';

        $name = $options['name'] . '[' . $options['field'] . ']';
        $class = $opts['class'];

        if($options['val']) {
            if($opts['filter']) {
                $value = $opts['filter']($options['val']);
            } else {
                $value = $options['val'];
            }
        } else {
            $value = $opts['default'];
        }

        if($opts['type']) {
            $opts['options'] = $opts['options'] ?? [];

            switch ($opts['type']) {
                case 'time':
                    $element = new Time('_', $opts['options'] + [
                        'options' => []
                    ]);
                    $element->setAttributes([
                        'data-type' => 'time',
                        'class' => 'std-select ' . $class,
                    ]);
                    break;
                default:
                    throw new \Exception('Unknown element type "' . $options['type'] . '"');
            }

            $element->setValue($value);

            $element->setName($name);

            $html = $this->getView()->formElement($element);
        } elseif(!empty($opts['options'])) {
            if(is_array($opts['options'])) {
                $select = new Select('_', ['options' => $opts['options']]);
            } else {
                $selectOpts = ['options' => $opts['options']];

                if($opts['placeholder']) {
                    $selectOpts['empty'] = $opts['placeholder'];
                } elseif(isset($opts['empty'])) {
                    $selectOpts['empty'] = $opts['empty'];
                }

                $select = new ETreeSelect('_', $selectOpts);
                $select->setAttributes([
                    'data-type' => 'collection',
                ]);
            }

            $select->setAttributes([
                'class' => 'std-select ' . $class
            ]);

            $select->setName($name);

            $select->setValue($value);

            $html .= $this->getView()->formElement($select);
        } else {
            $placeholder = !empty($opts['placeholder']) ? ' placeholder="' . $opts['placeholder'] . '"' : '';

            $nameAttr = ' name="' . $name . '"';

            $html .=
                '<input ' . $nameAttr . ' type="text" value="' . $value . '" class="std-input ' . $class . '"' . $placeholder . '">';
        }

        return $html;
    }
}