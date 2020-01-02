<?php
namespace Pipe\Google;

class Client
{
    static protected $instance = null;

    static public function getInstance()
    {
        if(!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected $client = null;

    public function getClient() {
        if($this->client) {
            return $this->client;
        }

        $serviceKeyDir = DATA_DIR . '/keys/google/service_account.json';

        $client = new \Google_Client();

        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $serviceKeyDir);

        $client->setApplicationName('GuteReise DB');
        $client->setAuthConfig($serviceKeyDir);
        $client->setScopes([\Google_Service_Calendar::CALENDAR, 'https://www.google.com/m8/feeds/']);

        return $this->client = $client;
    }
}