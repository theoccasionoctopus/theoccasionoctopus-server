<?php

namespace App\ActivityPub;

class APEvent
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getId()
    {
        return $this->data['id'];
    }
    public function getName()
    {
        return $this->data['name'];
    }
    public function getSummary()
    {
        return $this->data['summary'];
    }
    public function getURL()
    {
        return array_key_exists('url', $this->data) ? $this->data['url'] : null;
    }


    public function getStart(): \DateTime
    {
        $obj = new \DateTime($this->data['startTime']);
        return $obj;
    }

    public function getEnd(): \DateTime
    {
        $obj = new \DateTime($this->data['endTime']);
        return $obj;
    }
}
