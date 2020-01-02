<?php
namespace Hotels\Admin\View\Helper;

use Hotels\Admin\Model\HotelRoom;
use Pipe\Form\View\Helper\FormFactory;
use Zend\View\Helper\AbstractHelper;

class HotelRoomForm extends AbstractHelper
{
    public function __invoke(HotelRoom $room)
    {
        $factory = new FormFactory($this->getView(), $room->getForm());

        return $factory->structure([
            [
                ['width' => 20, 'element' => 'capacity'],
                ['width' => 40, 'element' => 'name'],
                ['width' => 40, 'element' => 'bed_size'],
            ],
            [
                'type'     => 'panel',
                'name'     => 'Цены',
                'children' => 'price',
            ],
        ]);
    }
}