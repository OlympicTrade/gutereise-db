<?php
namespace Hotels\Admin\Service;

use Pipe\Mvc\Service\Admin\TableService;
use Pipe\String\Numbers;
use Hotels\Admin\Model\Hotel;
use Hotels\Admin\Model\HotelRoom;

class HotelsService extends TableService
{
    const ERROR_ROOM_NOT_FOUND   = 501;
    const ERROR_HOTELS_PRICE     = 502;
    const NOTICE_HOTELS_CAPACITY = 503;

    public function delHotelRoom($roomId)
    {
        $room = new HotelRoom();
        $room->id($roomId);

        if($room->load()) {
            $room->remove();
        }
    }

    public function addHotelRoom($hotelId)
    {
        $newDay = new HotelRoom();
        $newDay->setVariables([
            'depend'      => $hotelId,
        ]);
        $newDay->save();

        return $newDay;
    }

    public function calcHotel($data)
    {
        $result = [
            'rooms'  => [],
            'errors' => [],
            'income' => 0,
            'outgo'  => 0,
        ];

        foreach ($data['rooms'] as $roomData) {
            if(!$roomData['id'] || !$roomData['tourists']) continue;
            $room = new HotelRoom(['id' => $roomData['id']]);
            $errors = [];
            if(!$room->load()) {
                $errors[self::ERROR_ROOM_NOT_FOUND] = 'Номер "' . $room->get('name') . '" не найден в базе';
            }

            $roomPrice = 0;

            $tourists = $roomData['tourists'];
            $capacity = $room->get('capacity');
            $desc = '';
            while ($tourists > 0) {
                $price = $room->getPrice($capacity, $data['date']);

                if(!$price) {
                    $errors[self::ERROR_HOTELS_PRICE] = 'Не найдена цена на номер "' . $room->get('name') . '"';
                    $tourists = 0; continue;
                }

                $roomsCount = intval($tourists / $capacity);

                $desc .= Numbers::declensionRu($roomsCount, ['номер', 'номера', 'номеров']) . ' * ' . $price . ', ';

                $roomPrice += intval($tourists / $capacity) * $price;

                $tourists = $tourists % $capacity;
                $capacity--;
            }

            $hotel = $data['hotel'];

            if($roomData['breakfast'] == Hotel::BREAKFAST_BUFFET) {
                $bfPrice = $hotel->get('breakfast')->buffet->price;
                if($bfPrice) {
                    $roomPrice += $roomData['tourists'] * $bfPrice;
                    $desc .= ' Завтраки: ' . $roomData['tourists'] . ' чел. * ' . $bfPrice;
                } else {
                    $desc .= ' Завтраки входят в стоимость';
                }
            } elseif($roomData['breakfast'] == Hotel::BREAKFAST_CONTINENTAL) {
                $bfPrice = $hotel->get('breakfast')->continental->price;
                if($bfPrice) {
                    $roomPrice += $roomData['tourists'] * $bfPrice;
                    $desc .= ' Завтраки: ' . $roomData['tourists'] . ' чел. * ' . $bfPrice;
                } else {
                    $desc .= ' Завтраки входят в стоимость';
                }
            }

            $touristPrice = $roomPrice / $roomData['tourists'];
            $result['rooms'][] = [
                'id'        => $room->id(),
                'name'      => $room->get('name'),
                'desc'      => rtrim($desc, ', '),
                'tourists'  => $roomData['tourists'],
                'breakfast' => $roomData['breakfast'],
                'bed_size'  => $roomData['bed_size'],
                'errors'    => $errors,
                'income'    => $roomPrice,
                'outgo'     => $roomPrice,
                'adult'     => $touristPrice,
                'child'     => $touristPrice,
            ];

            $result['income'] += $roomPrice;
            $result['outgo']  += $roomPrice;
        }

        return $result;
    }
}