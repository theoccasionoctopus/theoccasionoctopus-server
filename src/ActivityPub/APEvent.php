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
    public function getURL()
    {
        return $this->data['url'];
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
