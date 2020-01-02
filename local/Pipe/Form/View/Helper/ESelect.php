<?php
namespace Pipe\Form\View\Helper;

use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\EntityCollection;
use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;

class ESelect extends AbstractHelper
{
    /**
     * @var array
     */
    protected $validSelectAttributes = array(
        'name'      => true,
        'autofocus' => true,
        'disabled'  => true,
        'form'      => true,
        'multiple'  => true,
        'required'  => true,
        'size'      => true
    );

    /**
     * @var array
     */
    protected $validOptionAttributes = array(
        'disabled' => true,
        'selected' => true,
        'label'    => true,
        'value'    => true,
    );

    /**
     * @param ElementInterface $element
     * @param array $fields
     * @return string
     */
    public function render(ElementInterface $element, $fields = ['name'])
    {
        $attributes = $element->getAttributes();
        $value      = $element->getValue();
        $options    = $element->getOptions();

        $this->validTagAttributes = $this->validSelectAttributes;
        $html = '<select ' . $this->createAttributesString($attributes) .  '>';

        /** @var Entity $model */
        $model = null;
        /** @var EntityCollection $catalog */
        $catalog = null;

        if(!$options['options']) {
            $model   = $options['model'];
            $catalog = $model->getCollection(true);
        } else {
            $catalog = $options['options'];
            $model   = $catalog->getPrototype();
        }

        if($sort = $options['sort']) {
            $catalog->select()->order($sort);
        } else {
            if($model->hasProperty('name')) {
                $catalog->select()->order('name');
            }
        }

        if($options['before']) {
            foreach ($options['before'] as $key => $val) {
                $html .= '<option value="' . $key . '">' . $val . '</option>';
            }
        }

        if($options['empty'] !== null) {
            $html .= '<option value="">' . $options['empty'] . '</option>';
        }

        $this->validTagAttributes = $this->validOptionAttributes;
        $html .= $this->renderOptions($value, $catalog, $fields);

        $html .= '</select>';

        return $html;
    }

    /**
     * @param $value
     * @param $catalog
     * @param string $prefix
     * @return string
     */
    public function renderOptions($value, $catalog, $fields)
    {
        $html = '';

        foreach($catalog as $row) {
            $attributes = [];

            if($row->id() == $value) {
                $attributes['selected'] = true;
            }
			
			$optionText = '';
			foreach($fields as $field) {
				$sepPos = strpos($field, '-');
				$pluginName = substr($field, 0, $sepPos);
				if($pluginName) {
					$key = substr($field, $sepPos + 1);
					$optionText .= ' (' . $row->plugin($pluginName)->setId($row->get('partner_id'))->get($key) . ')';
				} else {
					$optionText .= $row->get($field) . ' ';
				}
			}

            $html .= '<option value="' . $row->id() . '"' . $this->createAttributesString($attributes) . '>' . $optionText . '</option>';
        }

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