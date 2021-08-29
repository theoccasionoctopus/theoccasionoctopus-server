<?php

namespace App\Message;

class NewUserMessage
{
    protected $user_id;


    public function __construct(int $user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }
}
