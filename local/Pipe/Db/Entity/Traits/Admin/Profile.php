<?php
namespace Pipe\Db\Entity\Traits\Admin;

trait Profile
{
    public function getPhones()
    {
        $result = [];
        foreach ($this->contacts['phones'] ?? [] as $phone) {
            if($phone) $result[] = $phone;
        }
        return $result;
    }

    public function getEmails()
    {
        $result = [];
        foreach ($this->contacts['emails'] ?? [] as $email) {
            if($email) $result[] = $email;
        }
        return $result;
    }
}