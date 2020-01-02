<?php
namespace Pipe\Calendar;

use Pipe\DateTime\Date;
use Zend\Json\Json;

class Calendar
{
    /** @var string */
    protected $calendarId;

    /** @var Date */
    protected $dt;

    public function __construct($id, $dt = null)
    {
        $this->calendarId = 'calendar-' . $id;
        $this->setDt($dt);

        $this->init();
    }

    public function getCalendarId()
    {
        return $this->calendarId;
    }

    public function setDt($dt)
    {
        $this->dt = $dt;

        return $this;
    }

    public function getDt()
    {
        return $this->dt;
    }

    public function save()
    {
        $data = Json::encode([
            'date' => $this->dt->format('Y-m-1'),
        ]);

        setcookie($this->calendarId, $data, time()+(3600*24*365), '/');

        return $this;
    }

    protected function init()
    {
        if($this->dt) {
            return $this;
        }

        try {
            if($_COOKIE[$this->calendarId]) {
                $data = Json::decode($_COOKIE[$this->calendarId]);
                $this->dt = Date::getDT($data->date);
            } else {
                $this->dt = Date::getDT('NOW');
            }
        } catch (\Exception $e) {
            $this->dt = Date::getDT('NOW');
        }

        return $this;
    }
}