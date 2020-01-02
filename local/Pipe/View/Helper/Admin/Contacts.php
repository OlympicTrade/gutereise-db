<?php
namespace Pipe\View\Helper\Admin;

use Zend\View\Helper\AbstractHelper;

class Contacts extends AbstractHelper
{
    public function __invoke($model)
    {
        $contacts = array_merge($model->getPhones(), $model->getEmails());

        return implode(', ', $contacts);
    }
}