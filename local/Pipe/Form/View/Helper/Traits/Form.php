<?php
namespace Pipe\Form\View\Helper\Traits;

use Pipe\Form\AdminForm;

trait Form
{
    /** @var AdminForm */
    protected $form;
    public function setForm($form) {
        $this->form = $form;
        return $this;
    }
}