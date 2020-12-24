<?php

namespace App\Message;

class NewUnfollowRemoteAccountMessage
{
    protected $account_id;
    protected $unfollows_account_id;

    public function __construct(string $account_id, string $unfollows_account_id)
    {
        $this->account_id = $account_id;
        $this->unfollows_account_id = $unfollows_account_id;
    }

    public function getAccountId(): string
    {
        return $this->account_id;
    }

    public function getUnfollowsAccountId(): string
    {
        return $this->unfollows_account_id;
    }
}
