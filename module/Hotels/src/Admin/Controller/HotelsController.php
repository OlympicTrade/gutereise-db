<?php
namespace Hotels\Admin\Controller;

use Pipe\Mvc\Controller\Admin\TableController;
use Hotels\Admin\Model\Hotel;
use Hotels\Admin\Model\HotelRoom;
use Zend\View\Model\JsonModel;

class HotelsController extends TableController
{
    protected function getViewStructure() {
        return [
            'list' => [
                'table' => [
                    'fields' => [
                        'name' => [
                            'header' => 'Название',
                        ],
                        'contacts' => [
                            'preset' => 'contacts',

                        ]
                    ],
                ],
            ],
            'edit' => [
                'table' => [],
            ],
        ];
    }

    public function addRoomAction()
    {
        $hotelId = $this->params()->fromPost('hid');
        $day = $this->getHotelsService()->addHotelRoom($hotelId);
        return new JsonModel(['html' => $this->viewHelper('HotelRoomForm', $day)]);
    }

    public function delRoomAction()
    {
        $roomId = $this->params()->fromPost('rid');
        $this->getHotelsService()->delHotelRoom($roomId);
        return new JsonModel(['status' => 1]);
    }

    public function getHotelInfoAction()
    {
        $hotel = new Hotel([
            'id' => $this->params()->fromPost('hid')
        ]);

        if(!$hotel->load()) return $this->send404();

        $resp = [
            'id'        => $hotel->id(),
            'name'      => $hotel->get('name'),
            'breakfast' => $hotel->getBreakfastOpts(),
            'rooms'     => []
        ];

        foreach ($hotel->plugin('rooms') as $room) {
            $resp['rooms'][] = [
                'id'        => $room->id(),
                'name'      => $room->get('name'),
                'bed_size'  => $room->get('bed_size'),
            ];
        }

        return new JsonModel($resp);
    }

    public function getRoomInfoAction()
    {
        $room = new HotelRoom([
            'id' => $this->params()->fromPost('rid')
        ]);

        if(!$room->load()) return $this->send404();

        $hotel = new Hotel(['id' => $room->id()]);

        return new JsonModel([
            'breakfast'   => $hotel->get('breakfast'),
            'name'        => $room->get('name'),
            'capacity'    => $room->get('capacity'),
            'bed_size'    => $room->get('bed_size'),
        ]);
    }

    /**
     * @return \Hotels\Service\HotelsService
     */
    protected function getHotelsService()
    {
        return $this->getServiceManager()->get('Hotels\Service\HotelsService');
    }
}