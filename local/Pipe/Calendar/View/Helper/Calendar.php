<?php
namespace Pipe\Calendar\View\Helper;

use Pipe\DateTime\Date;
use Pipe\String\Date as sDate;
use Zend\Form\Element\Select;
use Zend\View\Helper\AbstractHelper;

class Calendar extends AbstractHelper
{
    /** @var \Pipe\Calendar\Calendar */
    protected $calendar;

    /** @var array */
    protected $options;

    /** @var Date */
    protected $dt;

    public function __invoke(\Pipe\Calendar\Calendar $calendar, $options)
    {
        $this->setOptions($options);

        $this->calendar = $calendar;

        $this->dt = $calendar->getDt();

        return $this;
    }

    public function renderBase()
    {
        return
            '<div class="calendar-widget ' . $this->options['class'] . '" id="' . $this->calendar->getCalendarId() . '">'.
            $this->controls().
                '<div class="calendar" data-date="' . $this->dt->format() . '"><div class="page"></div></div>'.
            '</div>';
    }

    public function setOptions($options)
    {
        $this->options = $options + [
            'class'       => '',
            'controls'    => [],
            'calendar'    => [],
        ];

        return $this;
    }

    protected function controls()
    {
        $options = $this->options['controls'] + [
            'html'    => '',
        ];

        $year = (int) $this->dt->format('Y');
        $month = ltrim($this->dt->format('m'), '0');
        $option = [];
        for($i = $year - 5; $i <= $year + 2; $i++) {
            $option[$i] = $i;
        }

        $yElement = new Select('year', ['options'    => $option]);
        $yElement->setAttributes(['class' => 'year']);
        $yElement->setValue($year);

        $option = [];
        for($i = 1; $i <= 12; $i++) {
            $key = str_pad($i, 2, '0', STR_PAD_LEFT);
            $option[$key] = sDate::$months[$i];
        }

        $mElement = new Select('month', ['options' => $option]);
        $mElement->setAttributes(['class' => 'month']);
        $mElement->setValue($month);

        $view = $this->getView();
        $html =
            '<div class="controls">'.
                '<div class="prev"><</div>'.
                    $view->formSelect($yElement).
                    $view->formSelect($mElement).
                '<div class="next">></div>'.
                $options['html'].
            '</div>';

        return $html;
    }

    public function calendar()
    {
        $options = $this->options['calendar'] + [
            'day'    => function($dt, $controls, $calendar) {
                $html =
                    '<div class="day' . ($dt->format() == date('Y-m-d') ? ' today' : '') . '" data-date="' . $dt->format() . '">'.
                        $controls.
                        $calendar.
                    '</div>';

                return $html;
            },
            'header'  => function($dt) {
                /*$title =
                    sDate::$weekdaysShort[$dt->format('w')] . ' ' .
                    $dt->format('d') . ' ' . sDate::$monthsShort[ltrim($dt->format('m'), '0')] . '.';*/

                $title =
                    $dt->day();

                return '<div class="header">' . $title . '</div>';
            },
            'body'    => function($dt) {
                return '<div class="body"></div>';
            },
        ];

        $html =
            '';

        $dt = $this->dt;
        $cYear = $dt->format('Y');
        $cMonth = $dt->format('m');

        for($day = 1; $day <= cal_days_in_month(CAL_GREGORIAN, $cMonth, $cYear); $day++) {
            $dayDt = Date::getDT($cYear . '-' . $cMonth . '-' . $day);

            if($day == 1 && $dayDt->format('N') != 1) {
                for($i = 1; $i < $dayDt->format('N'); $i++) {
                    $html .=
                        '<div class="day disabled"></div>';
                }
            }

            $html .= call_user_func($options['day'], $dayDt,
                call_user_func($options['header'], $dayDt),
                call_user_func($options['body'], $dayDt)
            );
        }
        $html .=
                '<div class="clear"></div>'.
            '';

        return $html;
    }
}