<?php
namespace Pipe\View\Helper;

use Zend\View\Helper\AbstractHelper;

class CommonElements extends AbstractHelper
{
    public function __invoke($form, $options = ['name', 'contacts', 'comment'])
    {
        $html = '';
        $view = $this->getView();
        if(in_array('name', $options)) {
            $html .= $view->formRowset([[['width' => 100, 'element' => $form->get('name')]]], $form);
        }

        if(in_array('contacts', $options)) {
            $html .= $view->formRowset([
                [
                    ['width' => 50, 'element' => $form->get('contacts[phones][phone1]')],
                    ['width' => 50, 'element' => $form->get('contacts[phones][phone2]')],
                ],
                [
                    ['width' => 50, 'element' => $form->get('contacts[emails][email1]')],
                    ['width' => 50, 'element' => $form->get('contacts[emails][email2]')],
                ],
            ], $form);
        }

        if(in_array('comment', $options)) {
            $html .= $view->formRowset([[['width' => 100, 'element' => $form->get('comment')]]], $form);
        }

        return $html;
    }
}