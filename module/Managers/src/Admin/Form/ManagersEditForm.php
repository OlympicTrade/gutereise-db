<?php
namespace Managers\Admin\Form;

use Pipe\Form\Form\Admin\Form;

class ManagersEditForm extends Form
{
    public function init()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->addCommonElements(['id', 'name', 'contacts', 'comment']);
    }
}