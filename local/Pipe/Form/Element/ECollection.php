<?php
namespace Pipe\Form\Element;

use Zend\Form\Element;

class ECollection extends Element implements EntityAware
{
    public function setOptions($options) {
        $options = $options + [
            'sort' => true,
        ];

        return parent::setOptions($options);
    }
}
