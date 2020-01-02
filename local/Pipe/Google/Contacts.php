<?php
namespace Pipe\Google;

class Contacts
{
    /**
     * Options
     [
         'name' => '',
         'phone' => '',
         'email' => '',
         'desc' => ''
     ]
     *
     * @param $options
     * @param null || \Google_Client $client
     */
    public function addContact($options, $client = null)
    {
        if(MODE == 'dev') {
            return;
        }

        if(!$client) {
            $client = Client::getInstance()->getClient();
        }

        $token = $client->getAccessToken()['access_token'];

        $doc = new \DOMDocument();
        $doc->formatOutput = true;
        $entry = $doc->createElement('atom:entry');
        $entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:atom', 'http://www.w3.org/2005/Atom');
        $entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gd', 'http://schemas.google.com/g/2005');
        $entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gd', 'http://schemas.google.com/g/2005');
        $doc->appendChild($entry);

        $title = $doc->createElement('title', $options['name']);
        $entry->appendChild($title);

        $email = $doc->createElement('gd:email');
        $email->setAttribute('rel', 'http://schemas.google.com/g/2005#work');
        $email->setAttribute('address', $options['email']);
        $entry->appendChild($email);

        $contact = $doc->createElement('gd:phoneNumber', $options['phone']);
        $contact->setAttribute('rel', 'http://schemas.google.com/g/2005#work');
        $entry->appendChild($contact);

        $note = $doc->createElement('atom:content', $options['desc']);
        $note->setAttribute('rel', 'http://schemas.google.com/g/2005#kind');
        $entry->appendChild($note);

        $xmlToSend = $doc->saveXML();

        $url = 'https://www.google.com/m8/feeds/contacts/default/full/';

        $client = new \GuzzleHttp\Client();
        $resp = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-type'  => 'application/atom+xml',
                'Accept'        => 'application/atom+xml',
                'GData-Version' => '3.0',
                'charset'       => 'UTF-8',
            ],
            'body' => $xmlToSend
        ]);

        //echo $resp->getBody();
    }
}