<?php
namespace Application\Admin\View\Helper;

use Users\Admin\Model\User;
use Zend\View\Helper\AbstractHelper;

class PopupHeader extends AbstractHelper
{
    public function __invoke()
    {
        $view = $this->getView();

        $html = 
            '<div class="header">'.
                $view->header;

        if($view->headerBtns == 'tableEdit') {
            $html .= 
                '<div class="sidebar">'.
                    '<span class="btn item-save" title="Сохранить"><i class="fal fa-cloud-upload"></i></span>'.
                    '<span class="btn item-del" title="Удалить"><i class="fal fa-trash-alt"></i></span>'.
                    '<span class="btn close" data-fancybox-close><i class="fal fa-times"></i></span>'.
                '</div>';
        } else {
            $html .= 
                '<div class="sidebar">'.
                    '<span class="btn close" data-fancybox-close><i class="fal fa-times"></i></span>'.
                '</div>';
        }

        $html .= 
            '</div>';

        return $html;
    }
}