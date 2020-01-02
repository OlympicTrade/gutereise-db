<?php
namespace Drivers\Admin\Form;

use Pipe\Form\Form\Admin\Form;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class DriversEditForm extends Form
{
    public function init()
    {
        $this->addCommonElements(['id', 'fio', 'contacts', 'comment']);
    }
}