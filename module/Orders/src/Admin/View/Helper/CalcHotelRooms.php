<?php
namespace Orders\Admin\View\Helper;

use Zend\View\Helper\AbstractHelper;

class CalcHotelRooms extends AbstractHelper
{
    public function __invoke($form, $hotel)
    {
        $html =
            '<div class="hotel calc-block">'.
                '<div class="header">' . $hotel->get('name') . ' <span class="del"><i class="fas fa-times"></i></span></div>'.
                '<div class="body">'.
                    '<input type="hidden" name="hotels[hotels][' . $hotel->id() . '][id]" value="' . $hotel->id() . '">';

        $html .= $this->getView()->smartList([
            'class'  => '',
            'name'   => 'hotels[hotels][_HID_][rooms][_ID_]',
            'fields' => [
                [
                    'width'  => '45',
                    'el'     => $form->get('[id]'),
                ],
                [
                    'width'  => '15',
                    'el'     => $form->get('[tourists]'),
                ],
                [
                    'width'  => '15',
                    'el'     => $form->get('[breakfast]'),
                ],
                [
                    'width'  => '25',
                    'el'     => $form->get('[bed_size]'),
                ],
            ]
        ]);

        $html .=
                '</div>'.
            '</div>';

        return $html;
    }
}