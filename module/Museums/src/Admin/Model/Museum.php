<?php
namespace Museums\Admin\Model;

use Pipe\Db\Entity\Entity;
use Translator\Admin\Model\Translator;

class Museum extends Entity
{
    static public function getFactoryConfig() {
        return [
            'table'      => 'museums',
            'properties' => [
                'name'           => [],
                'contacts'       => ['type' => Entity::PROPERTY_TYPE_JSON],
                'comment'        => [],
                'proposal_title_plural' => [],
                'proposal_title' => [],
                'worktime_from'  => [],
                'worktime_to'    => [],
            ],
            'plugins'    => [
                'tickets' => function($model) {
                    return MuseumTickets::getEntityCollection();
                },
                'guides' => function($model) {
                    return MuseumGuide::getEntityCollection();
                },
                'weekends' => function($model) {
                    return MuseumWeekends::getEntityCollection();
                },
                'worktime' => function($model) {
                    return MuseumWorktime::getEntityCollection();

                },
                'extra' => function($model) {
                    return MuseumExtra::getEntityCollection();
                },
            ],
        ];
    }

    public function init($options)
    {
        Translator::setModelEvents($this, ['include' => ['proposal_title', 'proposal_title_plural']]);
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

    public function getUrl()
    {
        return '/museums/edit/' . $this->id() . '/';
    }
}