<?php
namespace Pipe\Form\Element;

use Pipe\Form\Form\Form;
use Zend\Form\Element;
use Zend\Form\Element\Text;

class EArray extends Element
{
    public function setOptions($options = []) {
        parent::setOptions($options);

        $this->form = new Form();
        $this->generateElements($options['elements'], $this->getName());
    }

    public function setValue($value)
    {
        $this->setArrayValue($value, $this->getName());

        return $this;
    }

    protected function setArrayValue($values, $prefix)
    {
        foreach ($values as $name => $value) {
            $fullName = $prefix . '[' . $name . ']';

            if(is_array($value)) {
                $this->setArrayValue($value, $fullName);
                continue;
            }

            if($this->form->has($fullName)) {
                $this->form->get($fullName)->setValue($value);
            }
        }
    }

    /** @var Form */
    protected $form;
    public function getForm() {
        return $this->form;
    }

    public function generateElements($data, $prefix) {
        foreach ($data as $name => $element) {
            $fullName = $prefix . '[' . $name . ']';

            if(is_array($element)) {
                if($element['element']) {
                    $element['element']->setName($fullName);
                    $this->form->add($element['element']);
                }

                $this->generateElements($element, $fullName);
                continue;
            }

            if(is_string($element)) {
                $this->form->add(new Text($prefix . '[' . $name . ']', [
                    'label' => $element,
                ]));
                continue;
            }

            if($element instanceof Element) {
                $element->setName($fullName);
                $this->form->add($element);
                continue;
            }

            throw new \Exception('unknown element type: ' . $element);
        }
    }
}
