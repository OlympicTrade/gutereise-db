<?php
namespace Guides\Admin\Model;

use Pipe\DateTime\Date;
use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\EntityCollection;
use Translator\Admin\Model\Translator;
use Users\Admin\Model\User;
use Zend\Db\Sql\Expression;

class Guide extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'guides',
            'properties' => [
                'name'      => [],
                'user_id'   => [],
                'contacts'  => ['type' => Entity::PROPERTY_TYPE_JSON],
                'price'     => [],
                'comment'   => [],
                'options'   => ['type' => Entity::PROPERTY_TYPE_JSON, 'default' => [
                    'calendar' => [
                        'status' => 'busy',
                    ]
                ]],
            ],
            'plugins'    => [
                'user' => [
                    'factory' => function($model){
                        return new User();
                    },
                    'independent' => true,
                ],
                'languages' => function($model){
                    return EntityCollection::factory(GuideLanguages::class);
                },
                'museums' => function($model){
                    return EntityCollection::factory(GuideMuseums::class);
                },
            ],
        ];
    }

    public function init($options) {
        Translator::setModelEvents($this, ['include' => ['name']]);
    }

    public function getPhones()
    {
        $phones = [];
        if(!$this->get('contacts')->phones) return $phones;
        foreach ($this->get('contacts')->phones as $phone) {
            if($phone) $phones[] = $phone;
        }
        return $phones;
    }

    public function getEmails()
    {
        $emails = [];
        if(!$this->get('contacts')->emails) return $emails;
        foreach ($this->get('contacts')->emails as $email) {
            if($email) $emails[] = $email;
        }
        return $emails;
    }

    public function isBusy(Date $dt)
    {
        $busy = $this->get('options')->calendar->status == 'busy';

        $day = new GuideCalendar();
        $day->select()->where([
            'date'     => $dt->format(),
            'depend'   => $this->id(),
        ]);

        if($day->load()) {
            $busy = $day->get('busy') == 'busy';
        }

        return $busy;
    }

    public function getPrice($duration, $time)
    {
        $duration =  max($duration, 4);

        $timeFrom = \DateTime::createFromFormat('H:i:s', $time);
        $timeTo = (clone $timeFrom)->modify('+' . $duration . ' hours');

        $daterange = new \DatePeriod($timeFrom, new \DateInterval('PT1H') ,$timeTo);

        //Ночью в 1.5 раза дороже
        $pph = $this->get('price');
        $price = 0;
        foreach($daterange as $dt){
            $hour = $dt->format('G');
            $price += ($hour >= 23 || $hour <= 8) ? $pph * 1.5 : $pph;
        }

        return $price;
    }

    public function getDebts()
    {
        $sql = $this->getSql();

        $select = $sql->select();
        $select->from(['odg' => 'orders_days_guides'])
            ->columns(['debt' => new Expression('SUM(odg.outgo)'), 'id'])
            ->join(['od' => 'orders_days'], 'od.id = odg.depend', ['date'])
            ->join(['o' => 'orders'], 'o.id = od.depend', ['order_name' => 'name', 'order_id' => 'id'])
            ->where(['odg.paid' => 0, 'odg.guide_id' => $this->id()])
            ->group('od.id')
            ->order('o.date_from')->order('o.id')->order('od.date');

        return $this->execute($select);
    }

    public function getUrl()
    {
        return '/guides/edit/' . $this->id() . '/';
    }
}






