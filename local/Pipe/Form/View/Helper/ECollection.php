<?php
namespace Pipe\Form\View\Helper;

use Pipe\Db\Entity\Entity;
use Pipe\Form\Element\Date;
use Pipe\Form\Element\ESelect;
use Pipe\Form\Element\Time;
use Zend\Form\Element\Select;
use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;

class ECollection extends AbstractHelper
{
    public function __invoke(ElementInterface $element = null)
    {
        $options = $element->getOptions();

        $items = $element->getValue();

        /*foreach ($items as $item) {
            d($item->price);
        }*/

        //$prefix     = $element->getName() . '[ecollection]';
        $prefix     = $element->getName();
        $formName   = $options['form'] ?? $prefix;
        $formClass  = $formName . '-short-form';
        $fields     = $options['fields'];

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
                $html .= ' <span class="btn sm i ' . $button['class'] . '"><i class="' . $button['icon'] . '"></i></span>';
            }
        }

        $html .=
                    '<span class="btn sm i blue item-add" data-form="#' . $prefix . $formName . '-form"><i class="fa fa-plus"></i></span>'
                .'</div>'
            .'</div>'
            .'<div class="list">';

        $i = 0;
        /** @var Entity $item */
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
                            'name'      => $elName,
                            'field'     => $field,
                            'opts'      => $opts,
                            'val'       => $item->getByTrace($field),
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
                        '<span class="btn sm i purple item-sort"><i class="fas fa-sort"></i></span>';
            }

            $html .=
                        '<span class="btn sm i red item-del"><i class="fa fa-times"></i></span>'
                        .'<span class="btn sm i blue item-copy"><i class="far fa-copy"></i></span>'
                    .'</div>'
                .'</div>';
        }

        $html .=
            '</div>'
        .'</div>';

        $html .=
            '<div class="item pattern">'; //style="display: none;" id="' . $prefix . '-' . $formName . '-form"

        foreach ($fields as $field => $opts) {
            /*if(strpos($field, '[') === false) {
                $elName = $prefix . '[_ID_]';
            } else {
                $elName = $prefix . '_ID_]';
            }*/

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
                        'field'     => $field,
                        'opts'      => $opts,
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
                    .'<input type="hidden" value="" name="' . $elName . '[id]">';

        if($options['sort']) {
            $html .=
                '<input type="hidden" class="sort" value="" name="' . $elName . '[sort]">'.
                '<span class="btn sm i purple item-sort"><i class="fas fa-sort"></i></span>';
        }

        $html .=
                    '<span class="btn sm i red item-del"><i class="fa fa-times"></i></span>'
                    .'<span class="btn sm i blue item-copy"><i class="far fa-copy"></i></span>'
                .'</div>'
            .'</div>';

        $html .=
        '</div>';

        return $html;
    }

    protected function renderElement($opts)
    {
        $opts = $opts + [
            'name'       => '',
            'val'        => '',
        ];

        $filedOpts = $opts['opts'] + [
            'attrs'      => [],
        ];

        $html = '';

        //$name = $opts['name'] . '[' . $opts['field'] . ']';

        if(strpos($opts['field'], '[') === false) {
            $name = $opts['name'] . '[' . $opts['field'] . ']';
        } else {
            $name = $opts['name'] . $opts['field'];
        }

        $class = $filedOpts['class'];

        if($opts['val'] === '' || $opts['val'] === null) {
            $value = $filedOpts['default'];
        } else {
            if($filedOpts['filter']) {
                $value = $filedOpts['filter']($opts['val']);
            } else {
                $value = $opts['val'];
            }
        }

        if($filedOpts['type']) {
            $filedOpts['options'] = $filedOpts['options'] ?? [];

            switch ($filedOpts['type']) {
                case 'time':
                    $element = new Time('_', $filedOpts['options'] + [
                        'options' => []
                    ]);

                    $element->setAttributes($filedOpts['attrs'] + [
                        'class' => 'std-select ' . $class,
                    ]);
                    break;
                case 'date':
                    $element = new Date('_', $filedOpts['options']);

                    $element->setAttributes($filedOpts['attrs'] + [
                            'class' => 'std-input ' . $class,
                        ]);
                    break;
                default:
                    throw new \Exception('Unknown element type "' . $opts['type'] . '"');
            }

            $element->setValue($value);

            $element->setName($name);

            $html = $this->getView()->formElement($element);
        } elseif(!empty($filedOpts['options'])) {
            if(is_array($filedOpts['options'])) {
                $select = new Select('_', ['options' => $filedOpts['options']]);
            } else {
                $selectOpts = ['options' => $filedOpts['options']];

                $selectOpts['empty'] = $filedOpts['placeholder'] ?? $filedOpts['empty'];

                $select = new ESelect('_', $selectOpts);
                $select->setAttributes([
                    'data-type' => 'collection',
                ]);
            }

            $select->setAttributes($filedOpts['attrs'] + [
                'class' => 'std-select ' . $class
            ]);

            $select->setName($name);
            $select->setValue($value);

            $html .= $this->getView()->formElement($select);
        } else {
            $placeholder = !empty($filedOpts['placeholder']) ? ' placeholder="' . $filedOpts['placeholder'] . '"' : '';

            $attrs = '';
            foreach ($filedOpts['attrs'] as $key => $val) {
                $attrs.= ' ' . $key . '="' . $val . '"';
            }

            $html .=
                '<input name="' . $name . '"' . $attrs . ' type="text" value="' . $value . '" class="std-input ' . $class . '"' . $placeholder . '">';
        }

        return $html;
    }
}