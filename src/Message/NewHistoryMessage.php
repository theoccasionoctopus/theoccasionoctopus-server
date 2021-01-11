<?php

namespace App\Message;

class NewHistoryMessage
{
    protected $history_id;


    public function __construct(string $history_id)
    {
        $this->history_id = $history_id;
    }

    public function getHistoryId(): string
    {
        return $this->history_id;
    }
}
