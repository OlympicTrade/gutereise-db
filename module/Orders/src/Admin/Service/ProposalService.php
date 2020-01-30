<?php

namespace Orders\Admin\Service;

use Application\Admin\Model\Language;
use Application\Admin\Model\Nationality;
use Pipe\DateTime\Time;
use Pipe\String\Date as StDate;
use Pipe\Mvc\Service\Admin\TableService;
use Drivers\Admin\Model\Driver;
use Excursions\Admin\Model\ExcursionDay;
use Guides\Admin\Model\Guide;
use Hotels\Admin\Model\Hotel;
use Hotels\Admin\Model\HotelRoom;
use Managers\Admin\Model\Manager;
use Museums\Admin\Model\Museum;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use Translator\Admin\Model\Translator;
use Transports\Admin\Model\Transport;

class ProposalService extends TableService
{
    public function getProposalData($data, $options = [])
    {
        $options = $options + [
            'br'    => '<br>',
        ];

        $br = $options['br'];

        $result = [
            'common'    => [],
            'days'      => [],
            'summary'   => [],
            'group'     => [],
        ];
        $commonStr = '';

        $tr = new Translator($data['kp_lang']);

        $currency = $data['currency']['currency'];

        switch ($currency) {
            case 'rub': $cSign = 'руб.'; break;
            case 'eur': $cSign = 'euro'; break;
            case 'usd': $cSign = 'usd';  break;
            default:
                throw new \Exception('Unknown currency type');
        }

        $totalSummary = $data['summary'][$currency];

        $language = (new Language())->setCode($data['lang_id']);

        $commonStr .= '<span data-link="group:clients">' .  $tr->tr('Язык') . ': ' . $tr->tr($language->get('name')) . '</span>' . $br;

        if($data['children']) {
            $touristsCountStr = $tr->declension($data['adults'], 'взрослый') . ' ' . $tr->tr('и') . ' ' . $tr->declension($data['children'], 'ребенок');
        } else {
            $touristsCountStr = $tr->declension($data['adults'], 'человек');
        }

        $result['group'] = $touristsCountStr;

        $commonStr .=
            '<span data-link="group:clients">' . $tr->tr('Количество гостей') . ': ' . $touristsCountStr . '</span>' . $br;

        $clients = [];
        $clientAndManager = '<span data-link="group:clients">';

        foreach ($data['clients'] as $client) {
            $clients[] = $client['name'] . ' ' . $client['phone'];
        }

        if(count($clients) > 0){
            if (count($clients) > 1) {
                $clientAndManager .= $tr->tr('Клиенты') . ':' . $br . implode($br, $clients) . $br;
            } else {
                $clientAndManager .= $tr->tr('Клиент') . ': ' . $clients[0] . $br;
            }
        }
        $clientAndManager .= '</span>';

        if($data['manager_id']) {
            $manager = new Manager();
            $manager->id($data['manager_id']);
            $clientAndManager .= '<span data-link="group:clients">' . $tr->tr('Куратор заявки') . ': ' .
                $tr->tr($manager->get('name')) . ' ' . $manager->getPhones()[0] . '</span>' . $br;
        }
        $commonStr .= $clientAndManager ? $br . $clientAndManager : '';

        //Этот кусок сравнивает, одни и теже гиды и водители выбраны в каждом дне или разные
        $daysGnD = [
            'guides'  => true,
            'drivers' => true,
        ];
        $gndIds = [];
        foreach ($data['days'] as $day) {
            $dayDt = [];
            foreach ($day['guides']['list'] as $guideData) {
                $dayDt['guides'][] = $guideData['guide_id'];
            }
            asort( $dayDt['guides']);

            foreach ($day['transports']['list'] as $transportData) {
                if($transportData['type'] == Transport::TYPE_WATER || !$transportData['driver_id']) continue;

                $dayDt['drivers'][] = $transportData['driver_id'];
            }
            asort( $dayDt['drivers']);
            $gndIds[] = $dayDt;
        }

        $gStr1 = '';
        $dStr1 = '';
        foreach ($gndIds as $dayIds) {
            if($dayIds['guides']) {
                $gStr2 = 'g' . implode('-', $dayIds['guides']);
                if ($gStr1 && $gStr1 != $gStr2) {
                    $daysGnD['guides'] = false;
                }
                $gStr1 = $gStr2;
            }

            if($dayIds['drivers']) {
                $dStr2 = 'd' . implode('-', $dayIds['drivers']);
                if ($dStr1 && $dStr1 != $dStr2) {
                    $daysGnD['drivers'] = false;
                }
                $dStr1 = $dStr2;
            }
        }

        if(isset($data['order']['proposal']['pricetable'])) {
            $orderPriceTable = $data['order']['proposal']['pricetable'];
        } else {
            $orderPriceTable = false;
        }

        $lastDate = '';
        $parentDay = null;
        $i = 0;
        foreach ($data['days'] as $dayKey => $day) {
            $i++;

            $dayResult = [
                'header'     => '',
                'timetable'  => '',
                'pricetable' => '',
                'contacts'   => '',
            ];

            $dt = new \DateTime($day['date'] . ' ' . $day['time']);
            $exDay = (new ExcursionDay())->id($day['day_id'])->load();

            $daySummary = $day['summary'][$currency];

            $sameDay = $lastDate == $dt->format('Y-m-d');

            if(!$sameDay) {
                $dayResult['header'] = $dt->format('d.m.Y') . ' (' . $tr->tr(mb_strtolower(StDate::$weekdays[$dt->format('N')])) . ')';
            }

            $lastDate = $dt->format('Y-m-d');

            $drivers = [];
            $guides = [];
            foreach ($day['transports']['list'] as $transportData) {
                if($transportData['driver_id']) {
                    $driver = new Driver();
                    $driver->id($transportData['driver_id']);
                    $drivers[] = $driver->get('name') . ' ' . $driver->getPhones()[0];
                }
            }

            if(!empty($day['guides']['list'])) {
                foreach ($day['guides']['list'] as $guideData) {
                    if($guideData['guide_id']) {
                        $guide = new Guide();
                        $guide->id($guideData['guide_id']);
                        $guides[] = $guide->get('name') . ' ' . $guide->getPhones()[0];
                    }
                }
            }

            if(count($guides) > 0){
                $guidesStr =
                    '<span data-link="day:'.$day['date'].'|tab:guides">';

                if (count($guides) > 1) {
                    $guidesStr .= $tr->tr('Гиды') . ':' . $br . implode($br, $guides);
                } else {
                    $guidesStr .= $tr->tr('Гид') . ': ' . $guides[0];
                }

                $guidesStr .=
                    '</span>' . $br;
            } else {
                $guidesStr = '';
            }

            if(count($drivers) > 0){
                $driverStr =
                    '<span data-link="day:'.$day['date'].'|tab:transports">';

                if(count($drivers) > 1) {
                    $driverStr .= $tr->tr('Водители') . ':' . $br . implode($br, $drivers);
                } else {
                    $driverStr .= $tr->tr('Водитель') . ': ' . $drivers[0];
                }

                $driverStr .=
                    '</span>' . $br;
            } else {
                $driverStr = '';
            }

            $gndDesc = '';
            if($daysGnD['drivers']) {
                $commonStr .= $i == 1 ? $driverStr : '';
            } else {
                $gndDesc .= $driverStr;
            }

            if($daysGnD['guides']) {
                $commonStr .= $i == 1 ? $guidesStr : '';
            } else {
                $gndDesc .= $guidesStr;
            }

            if(!$sameDay) {
                $dayResult['contacts'] .= $gndDesc;
            }

            /*if (!empty($day['proposal']['desc'])) {
                $dayStr .= $day['proposal']['desc'] . '<br>';
            }*/

            $totalDuration = Time::getDT();

            //Timetable
            $timeTable = $this->getTimeTable($day, $data, $exDay, $tr);
            $fromDt = Time::getDT($day['time']);
            $fromDt->modify('-15 minutes');

            $ttStr = [];
            foreach ($timeTable as $row) {
                $duration = Time::getDT($row['duration']);

                $str = $fromDt->format('H:i');
                $fromDt->addition($duration);

                if(!$duration->isEmpty() && $duration->format('H:i') > '00:15') {
                    $str .= ' - ' . $fromDt->format('H:i');
                }

                $str .= ' ' . $row['name'];

                $ttStr[] = $str;
            }
            $dayResult['timetable'] .= '<p data-link="day:'.$day['date'].'|anchor:timetable">' . implode($br, $ttStr) . '</p>';

            if($day['duration']) {
                $totalDuration = Time::getDT($day['duration']);
            }

            $durationStr = $totalDuration->getString();

            if(!$orderPriceTable) {
                $dayResult['date'] = $day['date'];
                $dayResult['pricetable'] .=
                    $br .
                    '<span data-link="day:'.$day['date'].'">' .
                    $tr->tr('Общее время экскурсии') . ' ' . $durationStr.

                    $br . $tr->tr('Стоимость при группе') . ' ' . $touristsCountStr .
                    ' ' . $tr->tr('составит') . ' ' . $daySummary['income'] . ' ' . $cSign . ' ';

                if ($data['children']) {
                    $dayResult['pricetable'] .=
                        '(' . $daySummary['adult'] . ' ' . $cSign . ' ' . $tr->tr('взрослый') . ', ' . $daySummary['child'] . ' ' . $cSign . ' ребенок)';
                } else {
                    $dayResult['pricetable'] .=
                        '(' . $daySummary['adult'] . ' ' . $cSign . ' ' . $tr->tr('с одного человека') . ')';
                }

                $dayResult['pricetable'] .=
                    '</span>';

                $priceTable = $this->getPriceTable($day, $data, $tr);

                $dayResult['pricetable'] .=
                    $br .
                    '<p data-link="day:'.$day['date'].'|anchor:pricetable">'.
                        $tr->tr('В стоимость включено') . ': '.
                        $br . '- ' . implode($br . '- ', $priceTable).
                    '</p>';
            }

            $result['days'][$i] = $dayResult;
        }

        $sumStr = '';

        if($orderPriceTable) {
            $sumStr .=
                $br . $br . $tr->tr('Общая стоимость тура при группе') . ' ' . $touristsCountStr . ' ' . $tr->tr('составит') . ' ' . $totalSummary['income'] . ' ' . $cSign . ' ';

            if($data['children']) {
                $sumStr .=
                    '(' . $totalSummary['adult'] . ' ' . $cSign . ' ' . $tr->tr('взрослый') . ', ' . $totalSummary['child'] . ' ' . $cSign . ' ребенок)';
            } else {
                $sumStr .=
                    '(' . $totalSummary['adult'] . ' ' . $cSign . ' ' . $tr->tr('с одного человека') . ')';
            }

            $sumStr .=
                $br . $tr->tr('В стоимость включено') . ':' . $br . '- ';

            $priceTable = $this->getOrderPriceTable($orderPriceTable, $data, $tr);

            $sumStr .= implode($br . '- ', $priceTable);
        }

        $hotelSum = '';
        if($data['hotels']) {
            foreach ($data['hotels']['hotels'] as $hotelData) {
                $hotel = new Hotel();
                $hotel->id($hotelData['id']);
                $daysCount = $data['hotels']['days_count'];

                $hotelSum .=
                    '<p data-link="group:clients|anchor:hotels">' .
                    'Проживание в гостинице ' . $hotel->get('name').
                    ($daysCount > 1 ? ' в течении ' . $daysCount . ' дней' : '');

                foreach ($hotelData['rooms'] as $roomData) {
                    $room = new HotelRoom();
                    $room->id($roomData['id']);

                    $roomsCount = ceil($roomData['tourists'] / $room->get('capacity'));

                    $hotelSum .=
                        $br.
                        $roomsCount . ' x ' . $room->get('name');

                    switch ($roomData['breakfast']) {
                        case Hotel::BREAKFAST_BUFFET:
                            $hotelSum .= ' + завтрак "Шведский стол"';
                            break;
                        case Hotel::BREAKFAST_CONTINENTAL:
                            $hotelSum .= ' + континентальный завтрак';
                            break;
                        default:
                            break;
                    }
                }
                $hotelSum .= '</p>';
            }
        }

        $result['summary'] = $sumStr;
        $result['hotel']   = $hotelSum;
        $result['common']  = $commonStr;

        return $result;
    }


    protected function dateToStr($date, Translator $tr)
    {
        list($hours, $minutes) = explode(':', $date);
        $hours   = ltrim($hours, '0');
        $minutes = ltrim($minutes, '0');

        $durationStr = '';
        if($hours) {
            $durationStr .= $tr->declension($hours, 'час');
        }
        if($minutes) {
            $durationStr .= ' ' . $tr->declension($minutes, 'минута');
        }

        return $durationStr;
    }

    public function getOrderPriceTable($orderPriceTable, $commonData, $translate = null)
    {
        $tr = $translate ?? new Translator('ru');
        $priceTable = [];

        foreach ($orderPriceTable['guides'] as $guideData) {
            $durationStr = $this->dateToStr($guideData['duration'], $tr);

            $priceTable[] =
                $tr->tr('Услуги персонального гида с ') . ' ' .
                $tr->tr(mb_strtolower(Language::getLanguage($commonData['lang_id'])->declension['5'])) . ' ' .
                $tr->tr('языком') . ' ' . $durationStr;
        }

        foreach ($orderPriceTable['transports'] as $transportData) {
            $transport = new Transport();
            $transport->id($transportData['transport_id']);

            $durationStr = $this->dateToStr($transportData['duration'], $tr);
            $priceTable[] = $tr->tr('Аренда') . ' ' . $transport->get('genitive1') . ' ' . $durationStr;
        }

        $priceTable[] = $tr->tr('Входные билеты в музеи согласно программе');

        foreach ($orderPriceTable['extra'] as $extraData) {
            $priceTable[] = $extraData['name'];
        }

        foreach ($commonData['days'] as $dayData) {
            if($dayData['museums']['list']) {
                foreach ($dayData['museums']['list'] as $museumData) {
                    $museum = new Museum();
                    $museum->id($museumData['museum_id'])->load();

                    if ($museumData['guides']) {
                        $priceTable[] = $tr->tr('Индивидуальное экскурсионное обслуживание гидом') . ' ' . $museum->get('proposal_title_plural');
                    }

                    if($museumData['extra']) {
                        foreach ($museumData['extra'] as $extra) {
                            if (!$extra['proposal_name']) continue;
                            $priceTable[] = $tr->tr($extra['proposal_name']);
                        }
                    }
                }
            }
        }

        return array_unique($priceTable);
    }

    public function getPriceTable($dayData, $commonData, $translate = null)
    {
        //dd($dayData);
        $tr = $translate ?? new Translator('ru');
        $priceTable = [];

        if($commonData['calc_type'] == 'proposal' || !$dayData['proposal']['pricetable']['autocalc']) {
            if($dayData['proposal']['pricetable']['list']) {
                foreach ($dayData['proposal']['pricetable']['list'] as $row) {
                    $priceTable[] = $tr->tr($row);
                }
            }
            return $priceTable;
        }

        if($dayData['guides']['list']) {
            $guidesPrTbl = [];

            foreach ($dayData['guides']['list'] as $guideData) {
                $str =
                    $tr->tr(mb_strtolower(Language::getLanguage($commonData['lang_id'])->declension['5'])).
                    ' языком ' . Time::getDT($guideData['duration'])->getString();
                $hash = crc32($str);

                if(isset($guidesPrTbl[crc32($str)])) {
                    $guidesPrTbl[$hash]['guides']++;
                } else {
                    $guidesPrTbl[$hash] = [
                        'guides' => 1,
                        'str'    => $str,
                    ];
                }
            }

            foreach ($guidesPrTbl as $row) {
                if($row['guides'] == 1) {
                    $priceTable[] =
                        $tr->tr('Услуги персонального гида с') . ' ' . $row['str'];
                } else {
                    $priceTable[] =
                        $tr->tr('Услуги') . ' ' . $tr->declension($row['guides'], 'персонального гида') . $tr->tr(' с ') . $tr->tr($row['str']);
                }
            }
        }

        $tmp = [];
        foreach ($dayData['transports']['list'] as $transport) {
            $tId = $transport['id'];
            if(isset($tmp[$tId])) {
                $tmp[$tId]['number']++;
            } else {
                $tmp[$tId] = $transport;
                $tmp[$tId]['number'] = 1;
            }
        }

        foreach ($tmp as $transportData) {
            if($transportData['errors']) continue;

            $transport = new Transport();
            $transport->id($transportData['id']);

            $priceRow = '';

            if($transportData['number'] > 1) {
                $numerals = [2 => 'двух', 3 => 'трех', 4 => 'четырех', 5 => 'пяти'];

                $priceRow .= $tr->tr('Аренда') . ' ' . $tr->tr($numerals[$transportData['number']]) .  ' ' . $transport->get('genitive2');
            } else {
                $priceRow .=
                    $tr->tr('Аренда') . ' ' . $tr->tr($transport->get('genitive1'));
            }

            $priceRow .=
                ' ' .
                Time::getDT($transportData['duration'])->getString();

            if($transport->get('type') == Transport::TYPE_AUTO && $dayData['car_delivery_time'] != '00:00:00') {
                $priceRow .=
                    ' + ' .
                    Time::getDT($dayData['car_delivery_time'])->getString() .
                    ' подачи';
            }


            $priceTable[] = $priceRow;
        }

        if($dayData['museums']['list']) {
            foreach ($dayData['museums']['list'] as $museumData) {
                $museum = new Museum();
                $museum->id($museumData['museum_id'])->load();

                $priceTable[] = $tr->tr('Входные билеты в') . ' ' . $museum->get('proposal_title') . '';

                if ($museumData['guides']) {
                    $priceTable[] = $tr->tr('Индивидуальное экскурсионное обслуживание гидом') . ' ' . $tr->tr($museum->get('proposal_title_plural'));
                }

                if($museumData['extra']) {
                    foreach ($museumData['extra'] as $extra) {
                        if (!$extra['proposal_name']) continue;
                        $priceTable[] = $tr->tr($extra['proposal_name']);
                    }
                }
            }
        }

        if($dayData['extra']['list']) {
            foreach ($dayData['extra']['list'] as $extraData) {
                if($extraData['proposal_name']) {
                    $priceTable[] = $tr->tr($extraData['proposal_name']);
                }
            }
        }

        if($dayData['day_id']) {
            $exDay = new ExcursionDay(['id' => $dayData['day_id']]);

            $excursionProp = $exDay->options['proposal']['price'];

            foreach (explode("\n", $excursionProp) as $row) {
                if(!$row) continue;
                $priceTable[] = $tr->tr($row);
            }
        }

        return array_unique($priceTable);
    }

    public function getTimeTable($dayData, $commonData, $exDay = null, $translate = null)
    {
        $tr = $translate ?? new Translator('ru');
        $timetable = [];

        if($commonData['calc_type'] == 'proposal' || !$dayData['proposal']['timetable']['autocalc']) {
            if($dayData['proposal']['timetable']['list']) {
                foreach ($dayData['proposal']['timetable']['list'] as $row) {
                    $timetable[] = [
                        'name'     => $tr->trt($row['name']),
                        'duration' => $row['duration'],
                    ];
                }
            }
            return $timetable;
        }

        $startDt = Time::getDT($dayData['time']);
        $dtTimer = clone $startDt;

        $transferDuration = Time::getDT('00:15');

        if($transferDuration->format('H:i') == '00:15') {
            $dtTimer->modify('-15 minutes');
        }

        if (!empty($dayData['proposal']['place_start'])) {
            $timetable[] = [
                'name'     => $tr->tr(rtrim($dayData['proposal']['place_start'], '.')),
                'duration' => $transferDuration->format(),
            ];
        } elseif(!empty($dayData['transports'])) {
            $timetable[] = [
                'name'     => $tr->tr('Встреча с гидом в холле гостиницы'),
                'duration' => $transferDuration->format(),
            ];
        } else {
            $timetable[] = [
                'name'     => $tr->tr('Встреча с гидом'),
                'duration' => $transferDuration->format(),
            ];
        }

        $dtTimer->addition($transferDuration);

        if ($exDay) {
            $eTimetable = $exDay->plugin('timetable', [
                'foreigners' => Nationality::langToNationality($commonData['lang_id']),
                'tourists' => ($commonData['adults'] + $commonData['children'])
            ]);

            if ($eTimetable->count()) {
                foreach ($eTimetable as $row) {
                    $dtDuration = Time::getDT($row->get('duration'));
                    if (!isset($dtTimer)) {
                        $dtTimer = clone $startDt;
                        $timetable[] = [
                            'name'     => $tr->tr($row->get('name')),
                            'duration' => $dtTimer->format('H:i:s'),
                        ];
                    } else {
                        $timetable[] = [
                            'name'     => $tr->tr($row->get('name')),
                            'duration' => $dtDuration->format('H:i:s'),
                        ];
                    }
                }
            }
        } elseif($dayData['museums']['list']) {
            foreach ($dayData['museums']['list'] as $museumData) {
                $museum = new Museum();
                $museum->id($museumData['museum_id'])->load();

                $dtDuration = Time::getDT($museumData['duration']);

                $timetable[] = [
                    'name' => $tr->tr('Экскурсия в ' . $museum->get('proposal_title')),
                    'duration' => $dtDuration->format('H:i:s'),
                ];
            }
        }

        if (!empty($dayData['proposal']['place_end'])) {
            $timetable[] = [
                'name'     => $tr->tr(rtrim($dayData['proposal']['place_end'], '.')),
                'duration' => '00:00:00',
            ];
        }

        return $timetable;
    }

    public function getProposalHtml($data)
    {
        $str = '';

        $str .= $data['common'];

        foreach ($data['days'] as $day) {
            if($day['header']) {
                $str .=
                    '<div style="text-align: center;">' .
                        '<h4><b data-link="day:' . $day['date'] . '">' . $day['header'] . '</b></h4>' .
                    '</div>';
            } else {
                $str .=
                    '<div style="border-bottom: 1px solid #000; overflow: hidden; height: 1px;"></div>';
            }

            if($day['contacts']) {
                $str .=
                    $day['contacts'];
            }

            $str .=
                $day['timetable'] . $day['pricetable'];
        }

        $str .=
            $data['summary'];

        $str .=
            $data['hotel'];

        $str = preg_replace('/\s\s+/', ' ', $str);

        return $str;
    }

    public function word($text, $file = false)
    {
        $text = str_replace(["\n", "\r", "\t"], '', $text);
        $text = str_replace(['h1', 'h2', 'h3', 'h4'], 'b', $text);

        $word = new PhpWord();
        $section = $word->addSection();
        Html::addHtml($section, $text);
        $writer = IOFactory::createWriter($word, 'Word2007');

        if(!$file) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment;filename="proposal.docx"');
            $writer->save('php://output');
            die();
        }

        $file = DATA_DIR . '/print.docx';
        $writer->save($file);
        return $file;
    }

    /**
     * @return \Orders\Admin\Service\CalcService
     */
    protected function getCalcService()
    {
        return $this->getServiceManager()->get('Orders\Admin\Service\CalcService');
    }
}