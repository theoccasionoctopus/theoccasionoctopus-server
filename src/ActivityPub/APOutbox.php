<?php

namespace App\ActivityPub;

use GuzzleHttp\Client;

class APOutbox
{
    public static function getAndCreate(string $url)
    {
        $guzzle = new Client(array('defaults' => array('headers' => array('User-Agent' => 'Prototype Software'))));

        $response = $guzzle->request("GET", $url, array('http_errors' => false));
        if ($response->getStatusCode() != 200) {
            throw new Exception("Could not get Outbox " . $url. " Response: ". $response->getStatusCode());
        }

        $outboxData = json_decode($response->getBody(), true);

        return new APOutbox($outboxData, $url);
    }


    protected $data;

    protected $url;


    public function __construct(array $data, string $url)
    {
        $this->data = $data;
        $this->url = $url;
    }


    public function getItems()
    {
        if ($this->data['type'] == 'OrderedCollection') {
            if (is_string($this->data['first'])) {
                return [];
            } else {
                $list = $this->data['first']['orderedItems'];
            }
        } elseif ($this->data['type'] == 'OrderedCollectionPage') {
            $list = $this->data['orderedItems'];
        }

        $out = [];
        foreach ($list as $item) {
            $out[] = new APItem($item);
        }
        return $out;
    }

    public function getNextURL()
    {
        if ($this->data['type'] == 'OrderedCollection') {
            if (array_key_exists('first', $this->data) && is_string($this->data['first'])) {
                return $this->data['first'];
            }
            // TODO if first is a OrderedCollectionPage and that has a next property return that
        } elseif ($this->data['type'] == 'OrderedCollectionPage') {
            if (array_key_exists('next', $this->data) && is_string($this->data['next'])) {
                return $this->data['next'];
            }
        }
    }
}
