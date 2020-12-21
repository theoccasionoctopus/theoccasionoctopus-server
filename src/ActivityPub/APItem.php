<?php

namespace App\ActivityPub;

class APItem
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function isTypeCreate(): bool
    {
        return $this->data['type'] == 'Create';
    }

    public function isObjectTypeEvent(): bool
    {
        return $this->data['object']['type'] == 'Event';
    }

    public function getObject(): APEvent
    {
        if ($this->data['object']['type'] == 'Event') {
            return new APEvent($this->data['object']);
        }
    }
}
