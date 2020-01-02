<?php
namespace Pipe\Form\View\Helper;

use Pipe\Db\Entity\EntityCollection;
use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;

class ECheckbox extends AbstractHelper
{
    /**
     * @param ElementInterface $element
     * @return string
     */
    public function render(ElementInterface $element)
    {
        /** @var EntityCollection $model */
        $model = $element->getOption('model');

        $entity = $model->getPrototype();

        if(!($compareField = $element->getOption('field'))) {
            $props = array_keys($entity->getProperties());
            unset($props[array_search('depend', $props)]);
            $compareField = array_shift($props);
        }

        $list = $element->getOption('collection');
        $list->select()->order('name');

        $vals = [];
        foreach ($element->getOption('model') as $val) {
            $vals[] = $val->get($compareField);
        }

        $html =
            '<div class="element-checkbox">'.
                '<input type="hidden" name="' . $element->getName() . '[echeckbox][field]" value="' . $compareField . '">';

        foreach ($list as $row) {
            $checked = in_array($row->id(), $vals) ? 'checked' : '';

            $html .=
                '<label class="' . $checked . '">' .
                    '<input type="checkbox" ' . $checked . ' name="' . $element->getName() . '[echeckbox][ids][]" value="' . $row->id() . '">'.
                    $row->get('name').
                '</label>';
        }

        $html .=
            '</div>';

        return $html;
    }

    /**
     * @param ElementInterface $element
     * @return $this|string
     */
    public function __invoke(ElementInterface $element = null, $fields = array('name'))
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element, $fields);
    }
}